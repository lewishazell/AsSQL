<?php
/**
 * @file PollState.php
 * @author Lewis Hazell
 * @license GPLv3
 */

/**
 * Represents the state of a MySQL link whilst being polled.
 */
final class PollState {
    private $objWrapper; //< The AsSQL wrapper for the link.
    private $funcCallback; //< The callback function to call once the query is complete.
    private $objTag; //< The tag specified in the call to AsSQL::beginQuery().

    /**
     * Gets the AsSQL wrapper for the link.
     *
     * @return
     *  The AsSQL wrapper for the link.
     */
    public function getWrapper() {
        return $this->objWrapper;
    }

    /**
     * Gets the underlying MySQLi connector.
     *
     * @return
     *  The underlying MySQLi connector.
     */
    public function getConnector() {
        return $this->objWrapper->getConnector();
    }

    /**
     * Creates a new instance of a PollState object.
     *
     * @param $objWrapper
     *  An AsSQL wrapper for the MySQLi connector.
     * @param $funcCallback
     *  The callback function to call when the query has been completed.
     * @param $objTag
     *  The tag specified in the call to AsSQL::beginQuery().
     */
    public function __construct($objWrapper, $funcCallback, $objTag) {
        $this->objWrapper = $objWrapper;
        $this->funcCallback = $funcCallback;
        $this->objTag = $objTag;
    }

    /**
     * Creates a new AsyncResult and calls the callback function.
     *
     * @param $objMySQLResult
     *  The result of the MySQL query.
     * @param $objExp
     *  Any exception that occured, of type mysqli_sql_exception, during the MySQL query.
     */
    public function callback($objMySQLResult, $objExp = null) {
        $objAsyncResult = new AsyncResult($objMySQLResult, $this->objTag, $objExp);
        $funcCallback = $this->funcCallback;
        $funcCallback($objAsyncResult);
    }
}

?>
