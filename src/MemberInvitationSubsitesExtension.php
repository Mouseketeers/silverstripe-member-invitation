<?php

namespace Mouseketeers\SilverstripeMemberInvitation;

use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Subsites\Model\Subsite;
use SilverStripe\View\SSViewer;

class MemberInvitationSubsitesExtension extends DataExtension
{
    private static $has_one = [
        'Subsite' => Subsite::class
    ];

    private static $defaults = [
        'SubsiteID' => 0
    ];

    public function populateDefaults()
    {
        if (!$this->owner->SubsiteID) {
            $this->owner->SubsiteID = (int) Subsite::currentSubsiteID();
            if (!$this->owner->SubsiteID) {
                $this->owner->SubsiteID = 0;
            }
        }
    }

    public function updateSummaryFields(&$fields)
    {
        $fields['Subsite.Title'] = 'Site';
    }  

    public function updateCMSFields(FieldList $fields)
    {
        // This hook can be called more than once during field construction.
        if ($fields->dataFieldByName('SubsiteID')) {
            return;
        }

        $subsiteField = DropdownField::create(
            'SubsiteID',
            'Site',
            Subsite::all_sites()->map('ID', 'Title')
        );

        if ($fields->dataFieldByName('Groups')) {
            $fields->insertAfter($subsiteField, 'Groups');
            return;
        }

        $fields->addFieldToTab('Root.Main', $subsiteField);
    }

    public function getInvitationSiteURL()
    {
        if (!$this->owner->SubsiteID) {
            return null;
        }

        $subsite = Subsite::get()->byID((int) $this->owner->SubsiteID);
        if (!$subsite) {
            return null;
        }

        $domain = $subsite->getPrimarySubsiteDomain();
        if (!$domain) {
            return null;
        }

        return $domain->absoluteBaseURL();
    }

    public function beforeSendInvitationEmail()
    {
        if (!$this->owner->SubsiteID) {
            return;
        }

        $subsite = Subsite::get()->byID((int) $this->owner->SubsiteID);
        if ($subsite && $subsite->Theme) {
            SSViewer::set_themes([$subsite->Theme]);
        }
    }
}
