<?php

namespace Cncw;

class Log {
    protected $qb;
    protected $total;
    protected $data;
    protected $pagination;
    public function __construct()
    {
        require preg_replace("/src\/Cncw/", "", __DIR__).'bootstrap.php';
        $this->qb = $conn->createQueryBuilder();
        $this->qb->select('*');
        $this->qb->from('circ_notif_wa_log');

        $vmid = new \Valitron\Validator($_GET);
        $vmid->rule('required', 'member_id');
        if($vmid->validate()) {
            $this->qb->where(
                $this->qb->expr()->like('member_id', ':member_id')
            );
            $this->qb->setParameter('member_id', '%' . $_GET['member_id'] . '%');
        }
        
        $vmnm = new \Valitron\Validator($_GET);
        $vmnm->rule('required', 'member_name');
        if($vmnm->validate()) {
            $this->qb->andWhere(
                $this->qb->expr()->like('member_name', ':member_name')
            );
            $this->qb->setParameter('member_name', '%' . $_GET['member_name'] . '%');
        }
        
        $vmph = new \Valitron\Validator($_GET);
        $vmph->rule('required', 'member_phone');
        if($vmph->validate()) {
            $this->qb->andWhere(
                $this->qb->expr()->like('member_phone', ':member_phone')
            );
            $this->qb->setParameter('member_phone', '%' . $_GET['member_phone'] . '%');
        }
        
        $vmtd = new \Valitron\Validator($_GET);
        $vmtd->rule('required', 'transaction_date');
        if($vmtd->validate()) {
            $this->qb->andWhere(
                $this->qb->expr()->like('transaction_date', ':transaction_date')
            );
            $this->qb->setParameter('transaction_date', '%' . $_GET['transaction_date'] . '%');
        }

        $total_res = $this->qb->execute()->rowCount();
        $this->setTotal($total_res);
        // Initialize a Data Pagination with previous count number
        $this->pagination = new \yidas\data\Pagination([
            'totalCount' => $this->getTotal(),
            'perPage' => 10,
        ]);

        $offset = $this->pagination->offset;
        $limit = $this->pagination->perPage;
        $this->qb->setFirstResult($offset);
        $this->qb->setMaxResults($limit);

        # SORT
        $vmso = new \Valitron\Validator($_GET);
        $vmso->rule('required', 'sort');
        $vmso->rule('required', 'orderBy');
        $vmso->rule('in', array('ASC', 'DESC'));
        if($vmso->validate()) {
            $this->qb->orderBy($_GET['orderBy'], $_GET['sort']);
        } else {
            $this->qb->orderBy('id', 'DESC');
        }

        $this->setData($this->qb->execute()->fetchAll());

    }

    protected function setTotal($total) {
        $this->total = $total;
    }

    public function setData($data) {
        $this->data = $data;
    }

    public function getTotal() {
        return $this->total;
    }

    public function getData() {
        return $this->data;
    }

    public function getPagination() {
        return $this->pagination;
    }

}
