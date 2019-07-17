<?php

namespace Cgit\MemberNetwork;

class EditMemberForm extends MemberForm
{
    /**
     * Form ID
     *
     * @var string
     */
    protected $id = 'edit_member_form';

    /**
     * Actions to perform on completion
     *
     * @return void
     */
    protected function done()
    {
        $this->updateNetworkMember();
    }
}
