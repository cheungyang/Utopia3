<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author robo
 */
interface DriverConnectionResolver
{
    /**
     * Resolves a driver connection to use for a particular SQL query.
     * 
     * @param string $sqlQuery The SQL query that is about to be executed.
     * @param array $allConnParams
     * @return Doctrine\DBAL\Driver\Connection The driver connection to use.
     */
    function resolveQueryConnection($sqlQuery);
    
    function resolveTransactionConnection();
}
