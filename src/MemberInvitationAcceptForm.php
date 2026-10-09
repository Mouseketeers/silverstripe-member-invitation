<?php

namespace Mouseketeers\SilverstripeMemberInvitation;


use Mouseketeers\SilverstripeMemberInvitation\MemberInvitation;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\ConfirmedPasswordField;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\ORM\ValidationException;
use SilverStripe\Core\Convert;

use SilverStripe\Security\Group;
use SilverStripe\Security\Member;

class MemberInvitationAcceptForm extends Form 
{
	public function __construct($controller, $name) {

		$hash = $controller->getRequest()->param('ID');
		$invite = MemberInvitation::get()->filter(['TempHash' => $hash])->first();

		$firstName = ($invite) ? $invite->FirstName : '';
		$surname = ($invite) ? $invite->Surname : '';
		$email = ($invite) ? $invite->Email : '';
		
		$fields = FieldList::create(
			HiddenField::create('Email', 'Email', $email),
			TextField::create(
				'FirstName',
				_t('MemberInvitation.ACCEPTFORM_FIRSTNAME', 'Name'),
				$firstName
			),
			ConfirmedPasswordField::create(
				'Password',
				_t('MemberInvitation.CREATE_PASSWORD', 'Create Password')
			),
			HiddenField::create('HashID')->setValue($hash)
		);
		if($surname) {
			$fields->insertAfter(
				'FirstName', 
				TextField::create(
					'Surname',
					_t('MemberInvitation.ACCEPTFORM_SURNAME', 'Surname'),
					$surname
				)
			);
		};
		$actions = FieldList::create(
			FormAction::create('acceptInvite', _t('MemberInvitation.ACCEPTFORM_REGISTER', 'Register'))
		);
		
		$required = RequiredFields::create('FirstName');
		
		// Session::set('MemberInvitation.accepted', true);
		
		
		$controller->extend('updateAcceptForm', $this, $fields, $actions, $required);
		
		parent::__construct($controller, $name, $fields, $actions, $required);
	}
    public function acceptInvite($data, Form $form)
    {
       
        if (!$invite = MemberInvitation::get()->filter(['TempHash' => $data['HashID']])->first()) {
            return $this->getController()->httpError(404, 'Invitation not found');
        }
        if ($form->validationResult()->isValid()) {

            $member = Member::create(['Email' => $invite->Email]);
            $form->saveInto($member);

            try {
                if($member->validate()) {
                    $member->write();
                    $groups = explode(',', $invite->Groups);
                    foreach (Group::get()->filter(['Code' => $groups]) as $group) {
                        $group->Members()->add($member);
                    }
                }
            }
            catch(ValidationException $e) {
                $form->sessionMessage(
                    $e->getMessage(),
                    'bad'
                );
                return $this->controller->redirectBack();
            }
            
            // $invite->delete();
            $invite->Accepted = true;
            $invite->write();
            
            return $this->controller->redirect($this->controller->Link('success'));
        } else {
            $form->sessionMessage(
                json_encode($form->getValidator()->getErrors()),
                'bad'
            );
            return $this->controller->redirectBack();
        }
    }
}
