<?php

namespace Mouseketeers\SilverstripeMemberInvitation;

use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use Mouseketeers\SilverstripeMemberInvitation\MemberInvitationFieldDetailForm_ItemRequest;


class MemberInvitationSecurityAdminExtension extends Extension 
{
    public function updateEditForm($form) {
        $fields = $form->Fields();
        $invitationsTab = $fields->findOrMakeTab('Root.Invitations', 'Invitations');
        $invitationsField = GridField::create('MemberInvitations',
            '',
            MemberInvitation::get(),
            GridFieldConfig_RecordEditor::create()
        );
        $invitationsTab->push($invitationsField);

        $invitationsField
            ->getConfig()
            ->getComponentByType(GridFieldDetailForm::class)
            ->setItemRequestClass(MemberInvitationFieldDetailForm_ItemRequest::class);

        $invitationsField->setForm($form);
    }
}