<?php
/**
 * @file AsyncResult.php
 * @author Lewis Hazell
 * @license GPLv3
 */

/**
 * Represents the result of the finished SQL query.
 */
final class AsyncResult {
    private $objTag; //< The tag specified in the call to AsSQL::beginQuery()
    private $objExp; //< Any exception that occurred during the query.
    private $objMySQLResult; //< The query result.
    private $objState;

    /**
     * Gets the tag specified in the call to AsSQL::beginQuery().
     *
     * @return
     *  The tag specified in the call to AsSQL::beginQuery().
     */
    public function getTag() {
        return $this->objTag;
    }

    /**
     * Creates a new instance of an AsyncResult object.
     *
     * @param $objMySQLResult
     *  The result from the MySQL query.
     * @param $objTag
     *  The tag specified in the call to AsSQL::beginQuery().
     * @param $objExp
     *  Any exception that occurred during the query.
     */
    public function __construct($objMySQLResult, $objState, $objTag, $objExp) {
        $this->objMySQLResult = $objMySQLResult;
        $this->objState = $objState;
        $this->objTag = $objTag;
        $this->objExp = $objExp;
    }

    /**
     * Signals an end to the query.
     *
     * If you are a user of this library; use AsSQL::endQuery() to end a query and not this function!
     *
     * @param $objState
     *  The PollState object used in the query. Required so that only AsSQL::endQuery() can end a query.
     *
     * @return
     *  The result of the MySQL query.
     */
    public function end($objState) {
        if($objState == $this->objState) {
            if($this->objExp != null) throw $this->objExp;
            return $this->objMySQLResult;
        }
        return false;
    }
}

?>
