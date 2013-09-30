<?php

class Iversia_FAQ_Model_Question extends XenForo_Model
{

    public function getById($faq_id, $moderation = 0)
    {
        return $this->_getDb()->fetchRow('
            SELECT f.*, c.title FROM xf_faq_question f
            LEFT JOIN xf_faq_category c ON (c.category_id = f.category_id)
            WHERE f.faq_id = ?', $faq_id);
    }

    public function getAll($fetchOptions = array())
    {
        $limitOptions   = $this->prepareLimitFetchOptions($fetchOptions);
        $orderClause    = $this->prepareUserOrderOptions($fetchOptions);

        $query = $this->fetchAllKeyed($this->limitQueryResults(
            'SELECT *, c.title
             FROM xf_faq_question
             LEFT JOIN xf_faq_category c ON (c.category_id = xf_faq_question.category_id)
             '. $orderClause .'
            ', $limitOptions['limit'], $limitOptions['offset']
        ), 'faq_id');

        return $query;
    }

    public function getAllCategory($category_id, $fetchOptions = array())
    {
        $limitOptions   = $this->prepareLimitFetchOptions($fetchOptions);
        $orderClause    = $this->prepareUserOrderOptions($fetchOptions, 'submit_date DESC');

        $query = $this->fetchAllKeyed($this->limitQueryResults(
            '
            SELECT *
             FROM xf_faq_question
             WHERE category_id = ?
             '. $orderClause .'
            ', $limitOptions['limit'], $limitOptions['offset']
        ), 'faq_id', $category_id);

        return $query;
    }

    public function prepareUserOrderOptions(array &$fetchOptions, $defaultOrderSql = '')
    {
        $choices = array(
            'question'      => 'sticky desc, question',
            'view_count'    => 'sticky desd, view_count',
            'submit_date'   => 'sticky desc, submit_date',
        );

        return $this->getOrderByClause($choices, $fetchOptions, $defaultOrderSql);
    }

    public function getTotal($moderation = 0)
    {
        return $this->_getDb()->fetchOne("
            SELECT COUNT(*) AS question_count
            FROM xf_faq_question
            WHERE moderation = ?
        ", array('moderation' => $moderation));
    }

    public function getViewTotal($moderation = 0)
    {
        return $this->_getDb()->fetchOne("
            SELECT SUM(view_count) AS view_count
            FROM xf_faq_question
            WHERE moderation = ?
        ", array('moderation' => $moderation));
    }

    public function getCategoryTotal($category_id, $moderation = 0)
    {
        return $this->_getDb()->fetchOne("
            SELECT COUNT(*) AS question_count
            FROM xf_faq_question
            WHERE moderation = ? and category_id = ?
        ", array('moderation' => $moderation, 'category_id' => $category_id));
    }

    public function getLatest($limit, $moderation = 0)
    {
        return $this->fetchAllKeyed("SELECT * FROM xf_faq_question ORDER BY submit_date DESC LIMIT $limit", 'faq_id');
    }

    public function getPopular($limit, $moderation = 0)
    {
        return $this->fetchAllKeyed("SELECT * FROM xf_faq_question ORDER BY view_count DESC LIMIT $limit", 'faq_id');
    }

    public function getSticky($limit, $moderation = 0)
    {
        return $this->fetchAllKeyed("SELECT * FROM xf_faq_question WHERE sticky = 1 ORDER BY view_count DESC LIMIT $limit", 'faq_id');
    }

    public function getQuestionsByIds(array $questionIds)
    {
        return $this->fetchAllKeyed('
            SELECT *
            FROM xf_faq_question
            WHERE faq_id IN ('.$this->_getDb()->quote($questionIds).')', 'faq_id');
    }

    public function getFaqIdsInRange($start, $limit)
    {
        $db = $this->_getDb();

        return $db->fetchCol($db->limit('
            SELECT faq_id
            FROM xf_faq_question
            WHERE faq_id > ?
            ORDER BY faq_id
        ', $limit), $start);
    }

    public function getByCategoryId($category_id)
    {
        return $this->fetchAllKeyed('SELECT * FROM xf_faq_question WHERE category_id = ? ORDER BY submit_date DESC', $category_id);
    }

    public function logQuestionView($faq_id)
    {
        $this->_getDb()->query('UPDATE xf_faq_question SET view_count = view_count+1 WHERE faq_id = ?', $faq_id);
    }

    public function deleteOrphanQuestions($category_id)
    {
        $this->_getDb()->query('DELETE FROM xf_faq_question WHERE category_id = ?', $category_id);
    }

    public function canManageFAQ()
    {
        $visitor = XenForo_Visitor::getInstance();

        if ($visitor->hasPermission('FAQ_Manager_Permissions', 'manageFAQ')) {
            return true;
        }

        return false;
    }

    public function canLikeFAQ()
    {
        $visitor = XenForo_Visitor::getInstance();

        if ($visitor->hasPermission('FAQ_Manager_Permissions', 'canLikeFAQ')) {
            return true;
        }

        return false;
    }
}
