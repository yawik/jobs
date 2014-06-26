<?php
/**
 * YAWIK
 *
 * @copyright (c) 2013-2014 Cross Solution (http://cross-solution.de)
 * @license   GPLv3
 */

namespace Jobs\Entity;

use Core\Entity\AbstractIdentifiableModificationDateAwareEntity as BaseEntity;
use Core\Entity\EntityInterface;
use Core\Entity\RelationEntity;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Core\Repository\DoctrineMongoODM\Annotation as Cam;
use Doctrine\Common\Collections\Collection;
use Auth\Entity\UserInterface;
use Core\Entity\Permissions;
use Core\Entity\PermissionsInterface;

/**
 * The job model
 *
 * @ODM\Document(collection="jobs", repositoryClass="Jobs\Repository\Job")
 */
class Job extends BaseEntity implements JobInterface {

    /**
     * uniq ID of a job posting used by applications to reference
     * a job
     *
     * @var String
     * @ODM\String @ODM\Index
     **/
    protected $applyId;
    
    /**
     * title of a job posting
     * 
     * @var String 
     * @ODM\String 
     */ 
    protected $title;
    
    /**
     * Description (Free text)
     * 
     * @var String
     * @ODM\String
     */
    protected $description;
    /**
     * name of the publishing company
     * 
     * @var String
     * @ODM\String
     */
    protected $company;
    
    /**
     * publishing company
     *
     * @var OrganizationInterface
     * @ODM\ReferenceOne (targetDocument="\Organizations\Entity\Organization", simple=true) @ODM\Index
     */
    protected $organization;
    
    
    /**
     * Email Adress, which is used to send notifications about e.g. new applications.
     * 
     * @var String
     * @ODM\String
     **/
    protected $contactEmail;
    
    /**
     * the owner of a Job Posting
     *  
     * @var UserInterface $user
     * @ODM\ReferenceOne(targetDocument="\Auth\Entity\User", simple=true) @ODM\Index
     */
    protected $user;
    
    /**
     * all applications of a certain jobad 
     * 
     * @var Collection \Applications\Entity\Application
     * @ODM\ReferenceMany(targetDocument="Applications\Entity\Application", simple=true, mappedBy="job")
     */
    protected $applications;
    
    /**
     * new applications
     * 
     * @ODM\ReferenceMany(targetDocument="Applications\Entity\Application", 
     *                    repositoryMethod="getUnreadApplications", mappedBy="job") 
     * @var Int
     */
    protected $unreadApplications;
    
    /**
     * location of the job posting
     * 
     * @var String
     * @ODM\String
     */
    protected $location;
    
    /**
     * Link which points to the job posting 
     * 
     * @var String
     * @ODM\String
     **/
    protected $link;
    
    /**
     * publishing date of a job posting
     * 
     * @var String
     * @ODM\Field(type="tz_date")
     */
    protected $datePublishStart;
    
    /**
     * Status of the job posting
     * 
     * @var String
     * @ODM\String
     */
    protected $status;
    
    /**
     * Reference of a jobad, on which an applicant can refer to.
     * 
     * @var String
     * @ODM\String 
     */
    protected $reference;
    
    /**
     * Unified Resource Locator to the company-Logo
     * 
     * @var String
     * @ODM\String 
     */
    protected $logoRef;
    
    /**
     * Unified Resource Locator the Yawik, which handled this job first - so 
     * does know who is the one who has commited this job.
     * 
     * @var String 
     * @ODM\String 
     */
    protected $uriPublisher;
    
    /**
     * this must be enabled to use applications forms etc. for this job or
     * to see number of applications in the list of applications
     * 
     * @var Boolean
     * 
     * @ODM\Boolean 
     */
    protected $camEnabled;
    
    /**
     * stores a list of lowercase keywords. This array can be regenerated by 
     *   bin/cam jobs generatekeywords
     * 
     * @ODM\Collection
     */
    protected $keywords;
    
    
    /**
     * Permissions
     * 
     * @var PermissionsInterface
     * @ODM\EmbedOne(targetDocument="\Core\Entity\Permissions")
     */
    protected $permissions;
    
    /**
     * Is this needed?
     * 
     * @return string
     */
    public function getResourceId()
    {
        return 'Entity/Jobs/Job';
    }
    /**
     * @see \Jobs\Entity\JobInterface::setApplyId()
     * @param String $applyId
     * @return \Jobs\Entity\JobInterface $job
     */
    public function setApplyId($applyId) {
        $this->applyId = (string) $applyId;
        return $this;
    }
    /**
     * @see \Jobs\Entity\JobInterface::getApplyId()
     * @return String
     */
    public function getApplyId() {
        return $this->applyId;
    }

    public function getTitle() {
        return $this->title;
    }

    /**
     * @see \Jobs\Entity\JobInterface::setTitle()
     * @param String $title
     * @return \Jobs\Entity\JobInterface $job
     */
    public function setTitle($title) {
        $this->title = (string) $title;
        return $this;
    }

    /**
     * @see \Jobs\Entity\JobInterface::setDescription()
     * @param String $text
     * @return \Jobs\Entity\JobInterface $job
     */
    public function setDescription($text)
    {
        $this->description = (string) $text;
        return $this;
    }
    
    /**
     * (non-PHPdoc)
     * @see \Jobs\Entity\JobInterface::getDescription()
     */
    public function getDescription()
    {
        return $this->description;
    }
    
    /**
     * (non-PHPdoc)
     * @see \Jobs\Entity\JobInterface::getCompany()
     */
    public function getCompany() {
        return $this->company;
    }

    /**
     * (non-PHPdoc)
     * @see \Jobs\Entity\JobInterface::setCompany()
     */
    public function setCompany($company) 
    {
        $this->company = $company;
        return $this;
    }
    
     /**
     * (non-PHPdoc)
     * @see \Jobs\Entity\JobInterface::getOrganization()
     */
    public function getOrganization() {
        return $this->organization;
    }
    
    /**
     * (non-PHPdoc)
     * @see \Jobs\Entity\JobInterface::setOrganization()
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;
        return $this;
    }
    
    
    /**
     * (non-PHPdoc)
     * @see \Jobs\Entity\JobInterface::getContactEmail()
     */
    public function getContactEmail() 
    {
        if (false !== $this->contactEmail && !$this->contactEmail) {
            $user = $this->getUser();
            $email = False;
            if (isset($user)) {
                $email = $user->getInfo()->getEmail();
            }
            $this->setContactEmail($email);
        }
        return $this->contactEmail;
    }
    /**
     * (non-PHPdoc)
     * @see \Jobs\Entity\JobInterface::setContactEmail()
     */
    public function setContactEmail($email)
    {
        $this->contactEmail = (string) $email;
        return $this;
    }
    /**
     * (non-PHPdoc)
     * @see \Jobs\Entity\JobInterface::setLocation()
     */
    public function setLocation($location)
    {
    	$this->location = $location;
    	return $this;
    }
    /**
     * (non-PHPdoc)
     * @see \Jobs\Entity\JobInterface::getLocation()
     */
    public function getLocation()
    {
    	return $this->location;
    }
    /**
     * (non-PHPdoc)
     * @see \Jobs\Entity\JobInterface::setUser()
     */
    public function setUser(UserInterface $user) {
        if ($this->user) {
            $this->getPermissions()->revoke($this->user, Permissions::PERMISSION_ALL, false);
        }
        $this->user = $user;
        $this->getPermissions()->grant($user, Permissions::PERMISSION_ALL);
        return $this;
    }
    /**
     * (non-PHPdoc)
     * @see \Jobs\Entity\JobInterface::getUser()
     */
    public function getUser() {
        return $this->user;
    }
    /**
     * (non-PHPdoc)
     * @see \Jobs\Entity\JobInterface::setApplications()
     */
    public function setApplications(Collection $applications) {
        $this->applications = $applications;
        return $this;
    }
    /**
     * (non-PHPdoc)
     * @see \Jobs\Entity\JobInterface::getApplications()
     */
    public function getApplications() {
        return $this->applications;
    }
    /**
     * Gets the number of unread applications
     * @return \Jobs\Entity\unknown
     */
    public function getUnreadApplications() {
        return $this->unreadApplications;
    }
    /**
     * (non-PHPdoc)
     * @see \Jobs\Entity\JobInterface::getLink()
     */
    public function getLink() {
        return $this->link;
    }
    /**
     * (non-PHPdoc)
     * @see \Jobs\Entity\JobInterface::setLink()
     */
    public function setLink($link) {
        $this->link = $link;
        return $this;
    }
    /**
     * (non-PHPdoc)
     * @see \Jobs\Entity\JobInterface::getDatePublishStart()
     */
    public function getDatePublishStart() {
        return $this->datePublishStart;
    }
    /**
     * (non-PHPdoc)
     * @param string $link
     * @see \Jobs\Entity\JobInterface::setDatePublishStart()
     */
    public function setDatePublishStart($datePublishStart) {
        $this->datePublishStart = $datePublishStart;
        return $this;
    }
    /**
     * (non-PHPdoc)
     * @see \Jobs\Entity\JobInterface::getStatus()
     */
    public function getStatus() {
        return $this->status;
    }
    /**
     * (non-PHPdoc)
     * @see \Jobs\Entity\JobInterface::setStatus()
     */
    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }
    /**
     * (non-PHPdoc)
     * @see \Jobs\Entity\JobInterface::getReference()
     */
    public function getReference() {
        return $this->reference;
    }
    /**
     * (non-PHPdoc)
     * @see \Jobs\Entity\JobInterface::setReference()
     */
    public function setReference($reference) {
        $this->reference = $reference;
        return $this;
    }
    /**
     * checks, wether a job is enabled for getting applications
     * @return boolean
     */
    public function getCamEnabled() {
        return $this->camEnabled;
    }
    /**
     * enables a job add to receive applications
     * 
     * @param boolean $camEnabled
     * @return \Jobs\Entity\Job
     */
    public function setCamEnabled($camEnabled) {
        $this->camEnabled = $camEnabled;
        return $this;
    }
    /**
     * returns an uri to the organisations logo
     * 
     * @return string
     */
    public function getLogoRef() {
        return $this->logoRef;
    }
    /**
     * Set the uri to the organisations logo
     * 
     * @param string $logoRef
     * @return \Jobs\Entity\Job
     */
    public function setLogoRef($logoRef) {
        $this->logoRef = $logoRef;
        return $this;
    }
    /**
     * 
     * @return string
     */
    public function getUriPublisher() {
        return $this->uriPublisher;
    }
    /**
     * 
     * @param unknown $uriPublisher
     * @return \Jobs\Entity\Job
     */
    public function setUriPublisher($uriPublisher) {
        $this->uriPublisher = $uriPublisher;
        return $this;
    }
    
    /**
     * @return array Names of attributes, which can be searched by keywords
     */
    public function getSearchableProperties()
    {
        return array('title', 'company', 'location', 'applyId', 'reference');
    }
    
    /**
     * (non-PHPdoc)
     * @see \Core\Entity\SearchableEntityInterface::setKeywords()
     */
    public function setKeywords(array $keywords)
    {
        $this->keywords = $keywords;
        return $this;
    }
    
    /**
     * (non-PHPdoc)
     * @see \Core\Entity\SearchableEntityInterface::getKeywords()
     */
    public function getKeywords()
    {
        return $this->keywords;
    }
    
    /**
     * (non-PHPdoc)
     * @see \Core\Entity\SearchableEntityInterface::clearKeywords()
     */
    public function clearKeywords()
    {
        $this->keywords = array();
        return $this;
    }
    
    /**
     * (non-PHPdoc)
     * @see \Core\Entity\PermissionsAwareInterface::getPermissions()
     */
    public function getPermissions()
    {
        if (!$this->permissions) {
            $permissions = new Permissions();
            if ($this->user) {
                $permissions->grant($this->user, Permissions::PERMISSION_ALL);
            }
            $this->setPermissions($permissions);
        }
        return $this->permissions;
    }
    
    /**
     * (non-PHPdoc)
     * @see \Core\Entity\PermissionsAwareInterface::setPermissions()
     */
    public function setPermissions(PermissionsInterface $permissions) {
        $this->permissions = $permissions;
        return $this;
    }
}