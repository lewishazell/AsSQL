<?php
/**
 * @file PollPool.php
 * @author Lewis Hazell
 * @license GPLv3
 */

/**
 * Represents the poll pool, which utilises the polling of all of the pending asynchronous MySQL queries.
 */
final class PollPool {
    private static $arrPolling = array(); //< An array of all of PollStates, containing connections with pending queries.

    /**
     * Gets the amount of asynchronous MySQL queries that are processing in the pool.
     *
     * @returns
     *  The amount of asynchronous MySQL queries that are processing in the pool.
     */
    public static function getCount() {
        return count(self::$arrPolling);
    }

    /**
     * Adds a PollState to the pool.
     *
     * @param $objPoll
     *  The PollState object to add.
     */
    public static function add($objPoll) {
        self::$arrPolling[] = $objPoll;
    }

    /**
     * Gets an array of all of the connectors that are being polled.
     *
     * @return
     *  An array of all of the connectors that are being polled.
     */
    private static function getConnectors() {
        $arrConnectors = array();
        foreach(self::$arrPolling as $objQuery) $arrConnectors[] = $objQuery->getConnector();
        return $arrConnectors;
    }

    /**
     * Gets a PollState object by it's underlying connector.
     *
     * @return
     *  The poll state if it exists, FALSE otherwise.
     */
    private static function getPollStateByConnector($objSearch) {
        foreach(self::$arrPolling as $objPoll) if($objPoll->getConnector() == $objSearch) return $objPoll;
        return false;
    }

    /**
     * Removes a PollState object from the pool.
     *
     * @param $objRemove
     *  The PollState object to remove.
     */
    private static function remove($objRemove) {
        foreach(self::$arrPolling as $intI=>$objPoll) if($objPoll == $objRemove) unset(self::$arrPolling[$intI]);
    }

    /**
     * Polls all of the connections in the pool.
     *
     * This needs to be called periodically, either in a loop or in a tick function.
     *
     * @param $intSec
     *  The amount of seconds to wait before continuing (default = 0 sec).
     * @param $intUSec
     *  The amount of milliseconds to wait before continuing if nothing happens (default = 100 usec)
     */
    public static function poll($intSec = 0, $intUSec = 100) {
        if(count(self::$arrPolling) == 0) return; // If there's nothing to be done, continue..
        $arrErrors = $arrReject = array();
        $arrRead = self::getConnectors(); // We will be looking at all of the connectors
        if(!mysqli::poll($arrRead, $arrErrors, $arrReject, $intSec, $intUSec)) return; // Poll everything
        // Handle all links that returned something
        foreach($arrRead as $objLink) {
            if($objQuery = self::getPollStateByConnector($objLink)) {
                self::remove($objQuery); // It's complete, we don't want to poll it again
                try {
                    $objResult = $objLink->reap_async_query(); // Reap the result
                    $objQuery->callback($objResult); // Send it to the callback function
                }catch(mysqli_sql_exception $objException){
                    $objQuery->callback(null, $objException); // Something happened reaping the query, an exception will be thrown on ending the query.
                }
            }
        }
        // Go through all connections that experienced errors
        foreach($arrErrors as $objLink) {
            $objQuery = self::getPollStateByConnector($objLink);
            self::remove($objQuery); // It's complete, we don't want to poll it again
            // Something happened in executing the query, an exception will be thrown on ending the query.
            $objQuery->callback(null, new mysqli_sql_exception($objLink->sqlstate, $objLink->error, $objLink->errno, __FILE__, __LINE__));
        }
        // Connections shouldn't ever get rejected under this system (all have async queries going), but just incase..
        foreach($arrReject as $objLink) {
            $objQuery = self::getPollStateByConnector($objLink);
            self::remove($objQuery);
        }
    }
}

?>
