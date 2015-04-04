<?php
/**
 * @file AsSQL.php
 * @author Lewis Hazell
 * @license GPLv3
 */

/**
 * A wrapper for mysqli connections to allow easier use of asynchronous queries.
 */
final class AsSQL {
    private $objConnector; //< Underlying MySQLi object, representing the connection.
    private $blnProcessing = false; //< Whether a query is currently being processed on this wrapper (only one can should be done at any time).

    /**
     * Gets the underlying MySQLi object which represents the connection.
     *
     * @returns
     *  The underlying MySQLi object which respresents the connection.
     */
    public function getConnector() {
        return $this->objConnector;
    }

    /**
     * Creates a new instance of an AsSQL object and connects to the database.
     *
     * @param $strHost
     *  The MySQL host.
     * @param $strUsername
     *  The username of the MySQL user.
     * @param $strPassword
     *  The password of the MySQL user.
     * @param $strDb
     *  The name of the MySQL database.
     * @param $intPort
     *  The port that the MySQL database server is running on.
     * @param $strSock
     *  Specifies the socket or named pipe that should be used.
     */
    public function __construct($strHost = '', $strUsername = '', $strPassword = '', $strDb = '', $intPort = -1, $strSock = '') {
        if($strHost == '') $strHost = ini_get('mysqli.default_host');
        if($strUsername == '') $strUsername = ini_get('mysqli.default_user');
        if($strPassword == '') $strPassword = ini_get('mysqli.default_pw');
        if($intPort == -1) $intPort = ini_get('mysqli.default_port');
        if($strSock == '') $strSock = ini_get('mysqli.default_socket');
        $this->objConnector = new mysqli($strHost, $strUsername, $strPassword, $strDb, $intPort, $strSock);
    }

    /**
     * Begins a MySQL query.
     *
     * @param $strSql
     *  The SQL query to process.
     * @param $funcCallback
     *  The callback function to call once the query is complete and we have a result.
     * @param $objTag
     *  An object to put in the resulting AsyncResult object, which can be collected by the callback function.
     */
    public function beginQuery($strSql, $funcCallback, $objTag) {
        if($this->blnProcessing == false) {
            $this->blnProcessing = true;
            $this->objConnector->query($strSql, MYSQLI_ASYNC);
            $objState = new PollState($this, $funcCallback, $objTag);
            PollPool::add($objState);
        }
    }

    /**
     * Ends a MySQL query.
     *
     * Any exception that happened during the query, of type mysqli_sql_exception, will be thrown.
     *
     * @param $objAsyncResult
     *  The async result object representing the finished query.
     */
    public function endQuery($objAsyncResult) {
        $this->blnProcessing = false;
        return $objAsyncResult->end();
    }
 
}

?>
