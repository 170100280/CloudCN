<?php
namespace util;
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/*
1- alterar insert de comentarios de acordo com nova tabela comments
2- acabar insert de users de acordo com a nova tabela autor
*/
class dbCloud
{
    const CREATE_SCHEMA_MYSQL_STATEMENT = "create schema if not exists `%s` default character set %s collate %s;";

    const CREATE_TABLE_COMMENTS = "
        create table if not exists `%s`.`%s` (
        id int not null auto_increment,
        comentario text,
        idAutor int,
        dataComent DATETIME,
        urlNoticia text,
        primary key (id)
    )";

    const CREATE_TABLE_AUTORES = "
    create table if not exists `%s`.`%s`(
    idAutor int not null auto_increment,
    email char(255),
    nome  char(255),
    password char(255),
    primary key (idAutor)
    )
    ";


    const MYSQL_DEFAULT_CHARSET = "utf8mb4"; //2019-02-14
    const MYSQL_DEFAULT_COLLATE = "utf8mb4_unicode_ci"; //2019-02-14

    const DEFAULT_SCHEMA_NAME = "clouddb";
    const TABLE_COMMENTS = "comments";
    const TABLE_AUTORES = "autor";

    const ACCEPTABLE_DB_ERRORS = [
        0, //no error
        1007, //database exists
        1050 //table exists
    ];

    //----------------------------------------------------------------------------
    private $mDB;

    private $mDBSchema;
    private $mDBUserName;
    private $mDBUserPassword;
    private $mDBHost;
    private $mDBPort;
    private $mSocketOrNamedPipe;

    private $mDBErr;
    private $mDBMsg;

    private $mEchoDbErrors; //20190318

    //----------------------------------------------------------------------------
    public function __construct(
        $pHost,
        $pUser,
        $pPass,
        $pSchema,
        $pPort,
        $pSocketOrNamedPipe

    )
    {
        $db =
            mysqli_connect(
                $pHost,
                $pUser,
                $pPass,
                null,//so it can connect to any schema/database in the instance
                //$pSchema, //received for other purposes, not for the connection purpose
                $pPort,
                $pSocketOrNamedPipe //specifies the socket or named pipe that should be used
            );

        $e = mysqli_connect_errno();
        $eM = mysqli_connect_error();

        if ($e !== 0) {
            $strMsg =
                sprintf(
                    "mysqli_connect error\nError code: %d\nError msg: %s\n",
                    $e,
                    $eM
                );
            //self::fb($strMsg);
        } else {
            $this->mDBHost = $pHost;
            $this->mDBUserName = $pUser;
            $this->mDBUserPassword = $pPass;
            $this->mDBPort = $pPort;
            $this->mDBSchema = $pSchema;
            $this->mSocketOrNamedPipe = $pSocketOrNamedPipe;

            $this->mEchoDbErrors = true;

            $strMsg = "SUCCESS in connecting to database!" . PHP_EOL;
            //self::fb($strMsg);
            //$this->mDB->close();
        }

        $this->mDB = $e === 0 ? $db : false;
    }//__construct

    //----------------------------------------------------------------------------
    public function dbGetInstallProcedure(
        $pbCreateSchema = false
    )
    {
        $ret = [];

        if ($pbCreateSchema) {
            $ret[] = trim(sprintf(
                self::CREATE_SCHEMA_MYSQL_STATEMENT,
                $this->mDBSchema,
                self::MYSQL_DEFAULT_CHARSET, //2019-02-14
                self::MYSQL_DEFAULT_COLLATE //2019-02-14
            ));
        }//if
        $ret[] = trim(sprintf(
            self::CREATE_TABLE_AUTORES,
            $this->mDBSchema,
            self::TABLE_AUTORES
        ));

        $ret[] = trim(sprintf(
            self::CREATE_TABLE_COMMENTS,
            $this->mDBSchema,
            self::TABLE_COMMENTS
        ));
        return $ret;
    }//dbGetInstallProcedure

    //----------------------------------------------------------------------------
    const ERRORS_DISPLAY_ONLY_THE_SINGLE_MOST_RECENT_ONE = -1;

    public function dbErrorsEcho(
        $piHowManyErrorsToShow = self::ERRORS_DISPLAY_ONLY_THE_SINGLE_MOST_RECENT_ONE
    )
    {
        $strMsg = "@" . __FUNCTION__ . ", " . date("Y-m-d H:i:s") . PHP_EOL;
        //self::fb($strMsg);

        $piHowManyErrorsToShow =
            $piHowManyErrorsToShow === -1 || $piHowManyErrorsToShow > count($this->mDBMsg) ?
                count($this->mDBMsg)
                :
                $piHowManyErrorsToShow;

        $iHowManyMsgs = count($this->mDBMsg);
        for ($i = $iHowManyMsgs - 1, $iErrorsShown = 0; $iErrorsShown < $piHowManyErrorsToShow; $i--, $iErrorsShown++) {
            $strMsg =
                sprintf(
                    "error code: %d | %s",
                    $this->mDBErr[$i],
                    $this->mDBMsg[$i]
                );

            $strMsg .= PHP_EOL;
            //self::fb($strMsg);
        }//for
    }//dbErrorsEcho

    //----------------------------------------------------------------------------
    public function dbInstall(
        $pbTryToExecuteInstallProcedure = true
    )
    {
        $bReturn = true;

        $installProcedure = $this->dbGetInstallProcedure(
            $pbTryToExecuteInstallProcedure
        );

        foreach ($installProcedure as $installStatement) {
            //self::fb($installStatement . "\n");

            /*
             * For successful SELECT, SHOW, DESCRIBE or EXPLAIN queries mysqli_query will return a mysqli_result
             * For other successful queries mysqli_query will return true and false on failure.
             */
            $queryResult = $this->mDB->query($installStatement);
            $e = mysqli_errno($this->mDB);
            $eM = mysqli_error($this->mDB);

            $bAcceptableError = array_search($e, self::ACCEPTABLE_DB_ERRORS) !== false;
            $bReturn = $bReturn && $bAcceptableError;

            $this->mDBErr[] = $e;
            $this->mDBMsg[] = $eM;

            if ($this->mEchoDbErrors) {
                $this->dbErrorsEcho(1);
            }
        }//foreach

        return $bReturn;
    }//dbInstall

    //_.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-.


    //_.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-.
    public function dbSelectWhereNomeUserTable(
        $pNomeUser
    )
    {
        if ($this->mDB)
        {

            $strPrePreparedStatement = sprintf(
                "select idAutor, email, nome from %s.%s where nome = ?",
                $this->mDBSchema,
                self::TABLE_AUTORES
            );

            //mysqli_stmt data type
            $mysqli_stmt_SelectUrl =
                $this->mDB->prepare(
                    $strPrePreparedStatement
                );

            $mysqli_stmt_SelectUrl->bind_param(
                "s",
                $pNomeUser
            );

            $bExecPreparedStatementResult = $mysqli_stmt_SelectUrl->execute();


            if ($bExecPreparedStatementResult) {
                //Gets a result set from a prepared statement
                $queryResult = $mysqli_stmt_SelectUrl->get_result();

                //$row = $queryResult->fetch_array(MYSQLI_ASSOC);

                $iHowManyResults = mysqli_num_rows($queryResult); //Gets the number of rows in a result but depends on $queryResult being of type mysqli_result
                //affected_rows : An integer greater than zero indicates the number of rows affected or retrieved
                $iHowManyResults = $mysqli_stmt_SelectUrl->affected_rows; //does NOT depend on the $query_result data type

                if ($iHowManyResults > 0) {
var_dump("entrou");
                    $mysqli_stmt_SelectUrl->close(); //only close after doing all necessary ops, including bind_result
                    //end of PREPARED STATEMENT pattern

                    //Returns an array of associative or numeric arrays holding result rows.
                    //MYSQLI_ASSOC, MYSQLI_NUM, or MYSQLI_BOTH.
                    $rows = mysqli_fetch_all(
                        $queryResult,
                        MYSQLI_ASSOC
                    );
                    /*
                    $idOfFirstProbablyOnlyRecordMatchingSelect = intval($rows[0]["id"]);

                    return $idOfFirstProbablyOnlyRecordMatchingSelect;
                    */
                    return true;
                }//if there were row results
            }//if prepared statement executed ok

        }//if
        return false;
    }//dbSelectWhereUrlExistsInDownloadsTable

    //_.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-.
    public function dbInsertComment(
        $pComentario,
        $pIdAutor,
        $urlNoti,
        $pbAcceptRepeats = true
    )
    {
        /*$aRowsWhereUrlExistsOrFalse = $this->dbSelectWhereUrlExistsInDownloadsTable($pUrl);

        $bCanProceed = $pbAcceptRepeats || (!$pbAcceptRepeats && $aRowsWhereUrlExistsOrFalse === false);
        $bCanProceed = true;
        $iInsertedAtThisId = false;
        if ($bCanProceed) //does not exist yet
        {*/


        $strNow = date("Y-m-d H:i:s");

        $strPrePreparedStatement = sprintf(
            "insert into `%s`.`%s` values (null, ?, ?, ?, ?)",
            $this->mDBSchema,
            self::TABLE_COMMENTS
        );
        $mysqli_stmt_insertUrl = $this->mDB->prepare(
        //"insert into `%s`.`%s` values (null, ?, ?, ?)"
            $strPrePreparedStatement
        );


        $mysqli_stmt_insertUrl->bind_param(
            "ssss",
            $pComentario, //url
            $pIdAutor,
            $strNow,
            $urlNoti,
        );



        $bExecResult = $mysqli_stmt_insertUrl->execute();
        $e = mysqli_errno($this->mDB);
        $eM = mysqli_error($this->mDB);


        if ($bExecResult) {

            $queryResult = $mysqli_stmt_insertUrl->get_result();

            $mysqli_stmt_insertUrl->close();
            //end of PREPARED STATEMENT pattern

            $iInsertedAtThisId = mysqli_insert_id($this->mDB);
        }//if correct execution

        $e = mysqli_errno($this->mDB);
        $eM = mysqli_error($this->mDB);
        $this->mDBErr[] = $e;
        $this->mDBMsg[] = $eM;

        if ($this->mEchoDbErrors) {
            $this->dbErrorsEcho(100);
        }

        return $iInsertedAtThisId;
        //}//if URL was not yet in database

        return false; //did not insert
    }//dbInsertComment


    public function insertNewUser($pEmail, $pAutorName,$pPassword)
    {
        $verificarSeExiste = $this->dbSelectWhereNomeUserTable($pAutorName);


        if($verificarSeExiste==false){

            $strPrePreparedStatement = sprintf(
                "insert into `%s`.`%s` values (null, ?, ?, ?)",
                $this->mDBSchema,
                self::TABLE_AUTORES
            );
            $mysqli_stmt_insertUrl = $this->mDB->prepare(
            //"insert into `%s`.`%s` values (null, ?, ?, ?)"
                $strPrePreparedStatement
            );

            $passwordHash = password_hash($pPassword,PASSWORD_DEFAULT);//hash da password

            $mysqli_stmt_insertUrl->bind_param(
                "sss",
                $pEmail,
                $pAutorName,
                $passwordHash
            );

            $bExecResult = $mysqli_stmt_insertUrl->execute();
            $e = mysqli_errno($this->mDB);
            $eM = mysqli_error($this->mDB);


            if ($bExecResult) {

                $queryResult = $mysqli_stmt_insertUrl->get_result();

                $mysqli_stmt_insertUrl->close();
                //end of PREPARED STATEMENT pattern

                $iInsertedAtThisId = mysqli_insert_id($this->mDB);
            }//if correct execution

            $e = mysqli_errno($this->mDB);
            $eM = mysqli_error($this->mDB);
            $this->mDBErr[] = $e;
            $this->mDBMsg[] = $eM;

            if ($this->mEchoDbErrors) {
                $this->dbErrorsEcho(100);
            }


            return false;
        }

        return true;

    }//insertNewUser

    public function dbSelectAllComments(
        $pUrl
    )
    {
        if ($this->mDB!==false)
        {
            $strPrePreparedStatement = sprintf(
                "SELECT comentario, dataComent, nome
                        FROM `%s`.`%s`
                        INNER JOIN `%s`.`%s`
                        ON clouddb.comments.idAutor=clouddb.autor.idAutor
                        where clouddb.comments.urlNoticia= ? ;",
                $this->mDBSchema,
                self::TABLE_COMMENTS,
                $this->mDBSchema,
                self::TABLE_AUTORES
            );

            //mysqli_stmt data type
            $mysqli_stmt_SelectUrl =
                $this->mDB->prepare(
                    $strPrePreparedStatement
                );

            $strMsg = "PREPARED @dbSelectWhereUrlExistsInDownloadsTable = $strPrePreparedStatement" . PHP_EOL;
            //self::fb($strMsg);

            $mysqli_stmt_SelectUrl->bind_param(
                "s",
                $pUrl
            );

            $bExecPreparedStatementResult = $mysqli_stmt_SelectUrl->execute();

            $strMsg = "EXECUTED prepared statement@dbSelectWhereUrlExistsInDownloadsTable" . PHP_EOL;
            //self::fb($strMsg);

            if ($bExecPreparedStatementResult) {

                //Gets a result set from a prepared statement
                $queryResult = $mysqli_stmt_SelectUrl->get_result();


                $iHowManyResults = mysqli_num_rows($queryResult); //Gets the number of rows in a result but depends on $queryResult being of type mysqli_result

                $iHowManyResults = $mysqli_stmt_SelectUrl->affected_rows; //does NOT depend on the $query_result data type

                if ($iHowManyResults > 0) {
                    $mysqli_stmt_SelectUrl->close(); //only close after doing all necessary ops, including bind_result
                    //end of PREPARED STATEMENT pattern

                    //Returns an array of associative or numeric arrays holding result rows.
                    //MYSQLI_ASSOC, MYSQLI_NUM, or MYSQLI_BOTH.
                    $rows = mysqli_fetch_all(
                        $queryResult,
                        MYSQLI_ASSOC
                    );
                    /*
                    $idOfFirstProbablyOnlyRecordMatchingSelect = intval($rows[0]["id"]);

                    return $idOfFirstProbablyOnlyRecordMatchingSelect;
                    */

                }//if there were row results
            }//if prepared statement executed ok
            $e = mysqli_errno($this->mDB);
            $eM = mysqli_error($this->mDB);
            $this->mDBErr[] = $e;
            $this->mDBMsg[] = $eM;

            if ($this->mEchoDbErrors) {
                $this->dbErrorsEcho(1);
            }
        }//if
        if(isset($rows)){
            return $rows;
        }else
        {
            return false;
        }

    }//dbSelectAllComments

    public function dbloginUser(
        $pUsername,
        $pPassword
    )
    {
        $verificarSeExiste = $this->dbSelectWhereNomeUserTable($pUsername);

        if($verificarSeExiste){

            $strPrePreparedStatement = sprintf(
                "SELECT password FROM `%s`.`%s` WHERE nome = ?",
                $this->mDBSchema,
                self::TABLE_AUTORES
            );
            $mysqli_stmt_insertUrl = $this->mDB->prepare(
            //"insert into `%s`.`%s` values (null, ?, ?, ?)"
                $strPrePreparedStatement
            );

           // $passwordHash = password_hash($pPassword,PASSWORD_DEFAULT);//hash da password

            $mysqli_stmt_insertUrl->bind_param(
                "s",
                $pUsername
            );

            $bExecResult = $mysqli_stmt_insertUrl->execute();
            $e = mysqli_errno($this->mDB);
            $eM = mysqli_error($this->mDB);


            if ($bExecResult) {

                $queryResult = $mysqli_stmt_insertUrl->get_result();


                $row = $queryResult->fetch_array(MYSQLI_ASSOC);
                //var_dump("row",$row["password"]);


                if(!password_verify($pPassword,$row["password"]))
                {
                    var_dump("errado");
                    return false;
                }
                $mysqli_stmt_insertUrl->close();
                //end of PREPARED STATEMENT pattern

                //$iInsertedAtThisId = mysqli_insert_id($this->mDB);
            }//if correct execution


            $e = mysqli_errno($this->mDB);
            $eM = mysqli_error($this->mDB);
            $this->mDBErr[] = $e;
            $this->mDBMsg[] = $eM;

            if ($this->mEchoDbErrors) {
                $this->dbErrorsEcho(100);
            }

            return true;
        }

    }//dbloginUser


}//dbCloud



/*$result = $db->dbInsert(
    //"https://something.net",
    $strTestUrl, //in production it will the exact URL used for the search request, here it can be any string for testing purposes
    "{bla bla}", //the "context"
    false //do NOT accept repeats
);*/
/* 
$strResult = $result === false ? "NOTHING INSERTED" : $result;
dbCloud::fb("The result of the insert was $strResult" . PHP_EOL); */
