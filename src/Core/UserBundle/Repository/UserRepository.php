<?php

namespace Core\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Core\UserBundle\Entity\UserIdeal;
use Core\CommonBundle\Helper\DataTablesStaticHelper;

class UserRepository extends EntityRepository {

    /**
     * Returns the count of every active users grouped by gender.
     * 
     * @return array
     */
    public function findAllActiveByGender() {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $users = $queryBuilder->select('u.gender, COUNT(u.id) as activeCount')
                ->from('CoreUserBundle:User', 'u')
                ->where('u.enabled = 1 AND u.deletedAt IS NULL')
                ->groupBy('u.gender')
                ->getQuery()
                ->getArrayResult();

        return $users;
    }

    /**
     * Searches for users who are not admins, the id isnt $userId, enabled, not deleted and fits for
     * the criterias in $params. Also returns the friendships (if exists) between the found users and the user with $userId.
     * 
     * @param int $userId
     * @param array $params
     * @return array
     */
    public function findAllByFiltersExceptAdminsAndGivenOne($userId, $params) {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('u.id as id, u.username as username, u.birthDate as birthDate, u.gender as gender, i.motto as motto, a.county as county, a.settlement as settlement, rf.status as requestedStatus, if.status as invitedStatus')
                ->from('CoreUserBundle:User', 'u')
                ->join('u.userIntroduction', 'i')
                ->join('u.address', 'a')
                ->leftJoin('u.requestedFriendships', 'rf', 'WITH', 'rf.invited = :userId')
                ->leftJoin('u.invitedFriendships', 'if', 'WITH', 'if.requester = :userId')
                ->where('u.enabled = 1 AND u.deletedAt IS NULL AND u.id <> :userId AND u.roles NOT LIKE :likeParam')
                ->setParameters(array(
                    'userId' => $userId,
                    'likeParam' => '%ADMIN%',
        ));

        $this->setFiltersForSearching($queryBuilder, $params);

        return $queryBuilder->getQuery()->getArrayResult();
    }

    /**
     * Returns all not deleted users for datatable ajax requests (include admins too).
     *
     * @param Array $parameters
     */
    public function getAllUsersDataTable($parameters) {
        /* 
         * Array of database columns which should be read and sent back to DataTables. 
         */
        $columns = array('id', 'username', 'enabled', 'createdAt', 'isAdmin');
        $selectArray = $this->getSelectArrayFromColumnsOfAllUsersDataTable($columns);

        $queryBuilder = $this->getQueryBuilderWithJoinsAndSelectsSetOfAllUsersDataTable($selectArray);

        /* Searching filter */
        $this->setSearchingOfAllUsersDataTable($queryBuilder, $parameters['search']['value']);
        /* Only non deleted users filter */
        $this->setFilteringOfAllUsersDataTable($queryBuilder);

        DataTablesStaticHelper::setPageOfResultAndOrderingForDataTables($queryBuilder, $parameters, $selectArray);

        /*
         * Get data to display
         */
        $result = $queryBuilder->getQuery()->getArrayResult();

        /* Data set length after filtering */
        $recordsFiltered = DataTablesStaticHelper::getCountOfFilteredTotalResultForDataTables($queryBuilder, 'u.id');

        /* Total data set length */
        $recordsTotal = DataTablesStaticHelper::getCountOfTotalResultForDataTables($this->getEntityManager(), $this->getTotalQueryOfAllUsersDataTable());

        return $this->processResultOfAllUsersDataTable($columns, $result, $parameters, $recordsTotal, $recordsFiltered);
    }

    /**
     * Iterates over $params and sets the required WHERE statementsfor $queryBuilder
     * according to the properties in $params.
     * 
     * @param QueryBuilder $queryBuilder
     * @param array $params
     */
    private function setFiltersForSearching($queryBuilder, $params) {
        foreach ($params as $property => $valueOrArray) {
            switch ($property) {
                case UserIdeal::FIELD_BODY_SHAPE:
                case UserIdeal::FIELD_EYE_COLOR:
                case UserIdeal::FIELD_HAIR_COLOR:
                case UserIdeal::FIELD_HAIR_LENGTH:
                case UserIdeal::FIELD_SEARCHING_FOR:
                case UserIdeal::FIELD_WANT_TO:
                    $queryBuilder->andWhere($this->getOrExprForFieldOfUserIntroduction($property, $valueOrArray));
                    break;
                case UserIdeal::FIELD_GENDER:
                    $queryBuilder->andWhere($this->getExprForGender($valueOrArray));
                    break;
                case UserIdeal::FIELD_ADDRESS:
                    $queryBuilder->andWhere('a.id = ' . $valueOrArray);
                    break;
                case 'county':
                    $queryBuilder->andWhere('a.county = \'' . $valueOrArray . '\'');
                    break;
                case UserIdeal::FIELD_AGE_FROM:
                    $queryBuilder->andWhere($this->getOlderThenOrEquealExprForBirthDate($valueOrArray));
                    break;
                case UserIdeal::FIELD_AGE_TO:
                    $queryBuilder->andWhere($this->getYoungerThenOrEquealExprForBirthDate($valueOrArray));
                    break;
                case UserIdeal::FIELD_HEIGHT_FROM:
                    $queryBuilder->andWhere($this->getGTEExprForFieldOfUserIntroduction('height', $valueOrArray));
                    break;
                case UserIdeal::FIELD_HEIGHT_TO:
                    $queryBuilder->andWhere($this->getLTEExprForFieldOfUserIntroduction('height', $valueOrArray));
                    break;
                case UserIdeal::FIELD_WEIGHT_FROM:
                    $queryBuilder->andWhere($this->getGTEExprForFieldOfUserIntroduction('weight', $valueOrArray));
                    break;
                case UserIdeal::FIELD_WEIGHT_TO:
                    $queryBuilder->andWhere($this->getLTEExprForFieldOfUserIntroduction('weight', $valueOrArray));
                    break;
                default:
                    break;
            }
        }
    }

    /**
     * Returns string for where statement to filter out users who are older than or 
     * equal $years old.
     * 
     * @param int $years
     * @return string
     */
    private function getOlderThenOrEquealExprForBirthDate($years) {
        $now = new \DateTime();
        $now->modify('-' . $years . 'years');

        return 'u.birthDate <= \'' . $now->format('Y-m-d') . '\'';
    }

    /**
     * Returns string for where statement to filter out users who are younger than or 
     * equal $years old.
     * 
     * @param int $years
     * @return string
     */
    private function getYoungerThenOrEquealExprForBirthDate($years) {
        $now = new \DateTime();
        $now->modify('-' . $years . 'years');

        return 'u.birthDate >= \'' . $now->format('Y-m-d') . '\'';
    }

    /**
     * Iterates over $values and returns a string for a where statement
     * to filter out users whose gender is one of those in $values.
     * 
     * @param array $values
     * @return string
     */
    private function getExprForGender($values) {
        $expressionPieces = array();
        foreach ($values as $value) {
            $expressionPieces[] = 'u.gender' . ' = \'' . $value . '\'';
        }

        return implode(' OR ', $expressionPieces);
    }

    /**
     * Returns expression for where statement to filter out users whose $field attribute
     * is greater then or equals to $value.
     * 
     * @param string $field
     * @param string $value
     * @return string
     */
    private function getGTEExprForFieldOfUserIntroduction($field, $value) {
        return 'i.' . $field . ' >= ' . $value;
    }

    /**
     * Returns expression for where statement to filter out users whose $field attribute
     * is less then or equals to $value.
     * 
     * @param string $field
     * @param string $value
     * @return string
     */
    private function getLTEExprForFieldOfUserIntroduction($field, $value) {
        return 'i.' . $field . ' <= ' . $value;
    }

    /**
     * Returns expression for where statement to filter out users whose $field attribute in their 
     * UserIntroduction is equals to one of the values in $values.
     * 
     * @param string $field
     * @param string $values
     * @return string
     */
    private function getOrExprForFieldOfUserIntroduction($field, $values) {
        $expressionPieces = array();
        foreach ($values as $value) {
            $expressionPieces[] = 'i.' . $field . ' = \'' . $value . '\'';
        }

        return implode(' OR ', $expressionPieces);
    }

    /*
     * ========================= Helper functions for getAllUsersDataTable()  =========================
     */

    /**
     * Returns an array containing the strings of the selects for the columns of getAllUsersTable(). Those column values which are base fields 
     * of the user entity simply prefixed with 'u.'. For isAdmin it's a CASE WHEN expression.
     * 
     * @param array $columns
     * @return array
     */
    private function getSelectArrayFromColumnsOfAllUsersDataTable($columns) {
        $selectColumns = array();

        $isAdmin = '(CASE WHEN (u.roles LIKE \'%ADMIN%\') THEN 1 ELSE 0 END) as isAdmin';

        foreach ($columns as $value) {
            if ('isAdmin' === $value) {
                $selectColumns[] = $isAdmin;
            } else {
                $selectColumns[] = 'u.' . $value;
            }
        }

        return $selectColumns;
    }

    /**
     * Creates and returns the queryBuilder for the getAllUsersTable() function. Also sets the required selects.
     * 
     * @param array $selectArray
     * @return QueryBuilder
     */
    private function getQueryBuilderWithJoinsAndSelectsSetOfAllUsersDataTable($selectArray) {
        $queryBuilder = $this->getEntityManager()
                ->getRepository('CoreUserBundle:User')
                ->createQueryBuilder('u')
                ->select(str_replace(" , ", " ", implode(", ", $selectArray)));

        return $queryBuilder;
    }

    /**
     * Sets the searching expressions for getAllUsersTable() function. If the $searchValue is not empty then it is search in name and email.
     * 
     * @param QueryBuilder $queryBuilder
     * @param string $searchValue
     */
    private function setSearchingOfAllUsersDataTable($queryBuilder, $searchValue) {
        if (is_null($searchValue) || empty($searchValue)) {
            return;
        }

        $queryBuilder->andWhere(
                        $queryBuilder->expr()->orX(
                                $queryBuilder->expr()->like('u.username', ':searchValue'), $queryBuilder->expr()->like('u.email', ':searchValue')
                        )
                )
                ->setParameter(':searchValue', '%' . $searchValue . '%');
    }

    /**
     * Sets the filtering expressions for getAllUsersTable() function (filters out deleted users).
     * 
     * @param QueryBuilder $queryBuilder
     */
    private function setFilteringOfAllUsersDataTable($queryBuilder) {
        $queryBuilder->andWhere($queryBuilder->expr()->isNull('u.deletedAt'));
    }

    /**
     * Returns the dql query which contains only the necessary filtering: user id for getAllUsersDataTable() function. 
     * This query can be used for retrieve the length of total results.
     * 
     * @return string
     */
    private function getTotalQueryOfAllUsersDataTable() {
        $totalQuery = 'SELECT u.id FROM CoreUserBundle:User u WHERE u.deletedAt IS NULL';

        return $totalQuery;
    }

    /**
     * Processes the raw result of the getAllUsersTable() into the format the frontend requires, it fills the data array of the outputArray.
     * 
     * @param array $columns
     * @param array $result
     * @param array $parameters
     * @param integer $recordsTotal
     * @param integer $recordsFiltered
     * @return array
     */
    private function processResultOfAllUsersDataTable($columns, $result, $parameters, $recordsTotal, $recordsFiltered) {
        $outputArray = DataTablesStaticHelper::getOutputArrayForDataTables($parameters, $recordsTotal, $recordsFiltered);

        foreach ($result as $aRow) {
            $aRow['isAdmin'] = $aRow['isAdmin'] === "1";
            
            $row = array();
            for ($i = 0; $i < count($columns); $i++) {
                /* General output */
                $row[$columns[$i]] = $aRow[$columns[$i]];
            }

            $outputArray['data'][] = $row;
        }

        return $outputArray;
    }

}
