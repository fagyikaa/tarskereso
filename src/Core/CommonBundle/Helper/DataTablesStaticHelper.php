<?php

namespace Core\CommonBundle\Helper;

/**
 * This class is not a service. The purpose of this class is that the common DataTables functions
 * which are needed in every DataTables repository function and are the same, be placed here avoiding 
 * code duplication. These functions should be static so easily can be used in every repository where needed.
 */
class DataTablesStaticHelper {

    /**
     * Returns the count of all the results of the given queryBuilder. The baseSelect must be a string with the base alias of the
     * queryBuilder and it's id field: 't.id' for example. 
     * 
     * 
     * @param QueryBuilder $queryBuilder
     * @param string $baseSelect
     * @return integer
     */
    public static function getCountOfFilteredTotalResultForDataTables($queryBuilder, $baseSelect) {
        return count($queryBuilder
                        ->select($baseSelect)
                        ->setFirstResult(null)
                        ->setMaxResults(null)
                        ->getQuery()->getResult());
    }

    /**
     * Returns the count of results for the given query.
     * 
     * @param string $queryString
     * @return integer
     */
    public static function getCountOfTotalResultForDataTables($entityManager, $queryString) {
        return count($entityManager
                        ->createQuery($queryString)
                        ->getResult());
    }

    /**
     * Returns the basic array what dataTables requires containing draw, recordsTotal and recordsFiltered. The data is empty because
     * processed and filled individually for the individual dataTables funcions.
     * 
     * @param array $parameters
     * @param integer $recordsTotal
     * @param integer $recordsFiltered
     * @return array
     */
    public static function getOutputArrayForDataTables($parameters, $recordsTotal, $recordsFiltered) {
        return array(
            "draw" => intval($parameters['draw']),
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "data" => array()
        );
    }
    
    /**
     * According to the parameters, which comes from dataTables sets the given queryBuilder to retrieve the 
     * required number of results, the offset of all results and the ordering of results. 
     * 
     * @param QueryBuilder $queryBuilder
     * @param array $parameters
     * @param array $selectArray
     */
    public static function setPageOfResultAndOrderingForDataTables($queryBuilder, $parameters, $selectArray) {
        //Selected page of results
        if (isset($parameters['start']) && $parameters['length'] != '-1') {
            $queryBuilder->setFirstResult((int) $parameters['start'])
                    ->setMaxResults((int) $parameters['length']);
        }

        /*
         * Ordering
         */
        if (isset($parameters['order'][0]['column'])) {
            $queryBuilder->orderBy($selectArray[(int) $parameters['order'][0]['column']], $parameters['order'][0]['dir']);
        }
    }

}
