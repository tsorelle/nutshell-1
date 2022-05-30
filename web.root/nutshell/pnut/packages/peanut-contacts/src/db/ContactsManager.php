<?php
namespace Peanut\contacts\db;

use Peanut\contacts\db\model\entity\Contact;
use Peanut\contacts\db\model\repository\ContactsRepository;
use Peanut\contacts\db\model\repository\EmailListsRepository;
use Peanut\contacts\db\model\repository\EmailSubscriptionAssociation;

class ContactsManager
{
    private $subscriptionsAssociation;

    private $contactsRepository;
    private function getContactsRepository ()
    {
        if (!isset($this->contactsRepository)) {
            $this->contactsRepository = new ContactsRepository();
        }
        return $this->contactsRepository;
    }

    private $emailListsRepository;
    private function getEmailListsRepository() {
        if (!isset($this->emailRepository)) {
            $this->emailListsRepository = new EmailListsRepository();
        }
        return $this->emailListsRepository;
    }


    public function getContactsAndLookups()
    {
        $result = new \stdClass();
        $contactsRepo = $this->getContactsRepository();
        $result->contacts = $contactsRepo->getContactList();
        $result->emailLists = $this->getEmailListsRepository()->getAll();
        $result->listingTypes = $contactsRepo->getListingTypes();
        return $result;
    }

    public function getContacts($filter=null,$activeOnly = true) {
        return $this->getContactsRepository()->getContactList($filter);
    }

    public function getContactSubscriptions($id)
    {
        $association = $this->getEmailSubscriptionsAssociation();
        return $association->getListValues($id);
    }

    private function getEmailSubscriptionsAssociation()
    {
        if (!isset($this->subscriptionsAssociation)) {
            $this->subscriptionsAssociation = new EmailSubscriptionAssociation();
        }
        return $this->subscriptionsAssociation;
    }

    public function updateContact($contactDTO, array $subscriptions = null)
    {
        $repo = $this->getContactsRepository();
        $isNew = empty($contactDTO->id);
        $contact = $isNew ? new Contact() : $repo->get($contactDTO->id);
        if (!$contact) {
            return false;
        }
        if (empty($contactDTO->sortkey)) {
            $name = $contactDTO->fullname;
            $parts = explode(' ',$contactDTO->fullname);
            $last = array_pop($parts);
            if (count($parts)) {
                $name = $last.', '.implode(' ',$parts);
            }
            $contactDTO->sortkey = $name;
        }
        $contact->assignFromObject($contactDTO);
        if (empty($contact->uid)) {
            $contact->uid = uniqid();
        }
        if ($isNew) {
            $id = $repo->insert($contact);
        }
        else {
            $repo->update($contact);
            $id = $contact->id;
        }
        if ($id === false) {
            return false;
        }
        $subscriptionsRepo = $this->getEmailSubscriptionsAssociation();
        if (is_array($subscriptions)) {
            $subscriptionsRepo->updateSubscriptions($id,$subscriptions);
        }
        return true;
    }

    public function setContactSiteAccount($contactId, $accountId)
    {
        $repo = $this->getContactsRepository();
        $contact = $repo->get($contactId);
        if (!$contact) {
            return false;
        }
        $contact->accountId = $accountId;
        $repo->update($contact);
        return true;
    }

}