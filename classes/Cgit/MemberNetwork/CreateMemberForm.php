<?php

namespace Cgit\MemberNetwork;

class CreateMemberForm extends MemberForm
{
    /**
     * Form ID
     *
     * @var string
     */
    protected $id = 'create_member_form';

    /**
     * Actions to perform on completion
     *
     * @return void
     */
    protected function done()
    {
        $this->createNetworkMember();
    }
}
