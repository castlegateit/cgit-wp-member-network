<?php

namespace Cgit\MemberNetwork;

abstract class MemberForm extends Form
{
    /**
     * Member instance
     *
     * @var Member
     */
    protected $member;

    /**
     * Initialize
     *
     * @return void
     */
    final protected function init()
    {
        $this->createNetworkMemberFields();
        $this->createNetworkMemberInstance();
    }

    /**
     * Create fields
     *
     * @return void
     */
    protected function createNetworkMemberFields()
    {
        $this->defaultFields = (new MemberFields)->fields();
    }

    /**
     * Create member instance
     *
     * @param integer $id
     * @return void
     */
    protected function createNetworkMemberInstance($id = null)
    {
        $this->member = new Member;

        if ($id) {
            $this->setNetworkMemberId($id);
        }
    }

    /**
     * Set member ID
     *
     * @param integer $id
     * @return void
     */
    public function setNetworkMemberId($id)
    {
        $this->member->setId($id);
        $this->values = $this->member->getValues();
    }

    /**
     * Create new member
     *
     * @return integer
     */
    protected function createNetworkMember()
    {
        if (!$this->canEditNetworkMember()) {
            return $this->nope();
        }

        $this->member->setValues($this->values);
        $this->member->create();

        return $this->member->getId();
    }

    /**
     * Edit existing member
     *
     * @return void
     */
    protected function updateNetworkMember()
    {
        if (!$this->canEditNetworkMember()) {
            return $this->nope();
        }

        $this->member->setValues($this->values);
        $this->member->update();
    }

    /**
     * Can the current user edit the target user?
     *
     * Network administrators can edit any network member. Non-administrators
     * can only edit their own values.
     *
     * @return boolean
     */
    public function canEditNetworkMember()
    {
        if ((new Roles)->isNetworkAdmin()) {
            return true;
        }

        return $this->member->getId() == (int) get_current_user_id();
    }

    /**
     * Block access
     *
     * @return void
     */
    protected function nope()
    {
        wp_die('Access denied');
    }
}
