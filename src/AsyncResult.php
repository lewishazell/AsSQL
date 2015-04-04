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
    public function __construct($objMySQLResult, $objTag, $objExp) {
        $this->objMySQLResult = $objMySQLResult;
        $this->objTag = $objTag;
        $this->objExp = $objExp;
    }

    /**
     * Signals an end to the query.
     *
     * This will throw any exception which happened during the query, of type mysqli_sql_exception.
     *
     * @return
     *  The result of the MySQL query.
     */
    public function end() {
        if($this->objExp != null) throw $this->objExp;
        return $this->objMySQLResult;
    }
}

?>
