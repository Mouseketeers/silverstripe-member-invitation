<?php

namespace Mouseketeers\SilverstripeMemberInvitation;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldDetailForm;

/**
 * Invitations are a managed model on SecurityAdmin (see _config/config.yml).
 * This hooks in the item request class that adds the "Send Invitation" action.
 */
class MemberInvitationSecurityAdminExtension extends Extension
{
    public function updateGridFieldConfig(GridFieldConfig $config): void
    {
        if ($this->getOwner()->getModelClass() !== MemberInvitation::class) {
            return;
        }
        $config
            ->getComponentByType(GridFieldDetailForm::class)
            ?->setItemRequestClass(MemberInvitationFieldDetailForm_ItemRequest::class);
    }
}
