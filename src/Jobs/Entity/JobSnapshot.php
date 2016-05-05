<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013 - 2016 Cross Solution (http://cross-solution.de)
 * @license   MIT
 * @author    weitz@cross-solution.de
 */

namespace Jobs\Entity;

use Core\Entity\Snapshot as BaseEntity;
use Auth\Entity\UserInterface;
use Doctrine\Common\Collections\Collection;
use Organizations\Entity\OrganizationInterface;
use Core\Exception\ImmutablePropertyException;
use Core\Entity\PermissionsInterface;
use Organizations\Entity\Organization;
use Core\Entity\ModificationDateAwareEntityInterface;
use Core\Entity\SnapshotInterface;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Core\Entity\EntityInterface;

/**
 * by using the BaseEntity,
 *
 * Class JobSnapshot
 * @package Jobs\Entity
 *
 * @ODM\EmbeddedDocument
 */
class JobSnapshot extends BaseEntity implements JobInterface, SnapshotInterface
{

    /**
     * @var String
     */
    protected $jobId;

    /**
     * unique ID of a job posting used by applications to reference
     * a job
     *
     * @var String
     * @ODM\Field(type="string") @ODM\Index
     **/
    protected $applyId;

    /**
     * title of a job posting
     *
     * @var String
     * @ODM\Field(type="string")
     */
    protected $title;

    /**
     * name of the publishing company
     *
     * @var String
     * @ODM\Field(type="string")
     */
    protected $company;

    /**
     * publishing company
     *
     * @var OrganizationInterface
     * @ODM\ReferenceOne (targetDocument="\Organizations\Entity\Organization", simple=true, inversedBy="jobs")
     * @ODM\Index
     */
    protected $organization;


    /**
     * Email Address, which is used to send notifications about e.g. new applications.
     *
     * @var String
     * @ODM\Field(type="string")
     **/
    protected $contactEmail;

    /**
     * the owner of a Job Posting
     *
     * @var UserInterface $user
     * @ODM\ReferenceOne(targetDocument="\Auth\Entity\User", simple=true)
     * @ODM\Index
     */
    protected $user;

    /**
     * all applications of a certain jobad
     *
     * @var Collection
     * @ODM\ReferenceMany(targetDocument="Applications\Entity\Application", simple=true, mappedBy="job",
     *                    repositoryMethod="loadApplicationsForJob")
     */
    protected $applications;

    /**
     * new applications
     *
     * @ODM\ReferenceMany(targetDocument="Applications\Entity\Application",
     *                    repositoryMethod="loadUnreadApplicationsForJob", mappedBy="job")
     * @var Int
     */
    protected $unreadApplications;

    /**
     * language of the job posting. Languages are ISO 639-1 coded
     *
     * @var String
     * @ODM\Field(type="string")
     */
    protected $language;

    /**
     * location of the job posting. This is a plain text, which describes the location in
     * search e.g. results.
     *
     * @var String
     * @ODM\Field(type="string")
     */
    protected $location;

    /**
     * locations of the job posting. This collection contains structured coordinates,
     * postal codes, city, region, and country names
     *
     * @var Collection
     * @ODM\EmbedMany(targetDocument="Location")
     */
    protected $locations;

    /**
     * Link which points to the job posting
     *
     * @var String
     * @ODM\Field(type="string")
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
     * end date of a job posting
     *
     * @var String
     * @ODM\Field(type="tz_date")
     */
    protected $datePublishEnd;

    /**
     * Status of the job posting
     *
     * @var Status
     * @ODM\EmbedOne(targetDocument="Status")
     * @ODM\Index
     */
    protected $status;

    /**
     * History on an job posting
     *
     * @var Collection
     * @ODM\EmbedMany(targetDocument="History")
     */
    protected $history;

    /**
     * Flag, privacy policy is accepted or not.
     *
     * @var bool
     * @ODM\Boolean
     */
    protected $termsAccepted;

    /**
     * Reference of a job opening, on which an applicant can refer to.
     *
     * @var String
     * @ODM\Field(type="string")
     */
    protected $reference;

    /**
     * Unified Resource Locator to the company-Logo
     *
     * @deprecated (use $organization->image->uri instead)
     * @var String
     * @ODM\Field(type="string")
     */
    protected $logoRef;

    /**
     * Template-Name
     *
     * @var String
     * @ODM\Field(type="string")
     */
    protected $template;

    /**
     * Application link.
     *
     * @var String
     * @ODM\Field(type="string")
     */
    protected $uriApply;

    /**
     * Unified Resource Locator the Yawik, which handled this job first - so
     * does know who is the one who has commited this job.
     *
     * @var String
     * @ODM\Field(type="string")
     */
    protected $uriPublisher;

    /**
     * The ATS mode entity.
     *
     * @var AtsMode
     * @ODM\EmbedOne(targetDocument="AtsMode")
     */
    protected $atsMode;

    /**
     * this must be enabled to use applications forms etc. for this job or
     * to see number of applications in the list of applications
     *
     * @var Boolean
     *
     * @ODM\Boolean
     */
    protected $atsEnabled;

    /**
     * Permissions
     *
     * @var PermissionsInterface
     * @ODM\EmbedOne(targetDocument="\Core\Entity\Permissions")
     */
    protected $permissions;

    /**
     *
     * @var TemplateValues
     * @ODM\EmbedOne(targetDocument="\Jobs\Entity\TemplateValues")
     */
    protected $templateValues;


    /**
     * Can contain various Portals
     *
     * @var array
     * @ODM\Collection*/
    protected $portals = array();

    /**
     * Flag indicating draft state of this job.
     *
     * @var bool
     * @ODM\Boolean
     */
    protected $isDraft = false;

    /**
     * @param $jobEntity
     */
    public function __construct()
    {
    }

    /**
     * transfer all attributes from the job-entity to the snapshot-entity
     *
     * @TODO this could go into an abstract class since it is nearly allways the same
     *
     * @param $source
     * @param $target
     * @return $this
     */
    protected function copyAttributes($source, $target)
    {
        $methods = array_filter(
            get_class_methods($source),
            function ($v) {
                return 3 < strlen($v) && strpos($v, 'get') === 0;
            }
        );
        // these attributes don't need to get copied
        $methods = array_diff($methods, array('getId', 'getHydrator', 'getHiringOrganizations'));
        $methods = array_map(
            function ($v) {
                return lcfirst(substr($v, 3));
            },
            $methods
        );
        foreach ($methods as $attribute) {
            $element = $source->$attribute;
            if (isset($element)) {
                // when the parameter is rigid you can't assign an non-existing elements
                if (method_exists($target, 'set' . lcfirst($attribute))) {
                    $target->$attribute = $element;
                }
            }
        }
        return $this;
    }



    /**
     * Gets the unique key used by applications to reference a job posting
     *
     * @param string $applyId
     * @throws \Core\Exception\ImmutablePropertyException
     */
    public function setApplyId($applyId)
    {
        throw new ImmutablePropertyException('applyId', $this);
    }

    /**
     * Sets a unique key used by applications to reference a job posting
     *
     * @return string
     */
    public function getApplyId()
    {
        return $this->applyId;
    }

    /**
     * checks, weather a job is enabled for getting applications
     * @deprecated since 0.19 - Use atsMode sub document via getAtsMode()
     * @return boolean
     */
    public function getAtsEnabled()
    {
        return $this->atsEnabled;
    }

    /**
     * enables a job add to receive applications
     *
     * @param boolean $atsEnabled
     * @deprecated since 0.19 - Use atsMode entity via setAtsMode()
     * @throws \Core\Exception\ImmutablePropertyException
     * @return \Jobs\Entity\Job
     */
    public function setAtsEnabled($atsEnabled)
    {
        throw new ImmutablePropertyException('atsEnabled', $this);
    }

    /**
     * Sets the ATS mode.
     *
     * @param AtsMode $mode
     * @throws \Core\Exception\ImmutablePropertyException
     *
     * @return self
     * @since 0.19
     */
    public function setAtsMode(AtsMode $mode)
    {
        throw new ImmutablePropertyException('atsMode', $this);
    }

    /**
     * Gets the ATS mode.
     *
     * @return AtsMode
     * @since 0.19
     */
    public function getAtsMode()
    {
        return $this->atsMode;
    }

    /**
     * Gets an URI for a job posting
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Sets an URI for a job posting
     * @throws \Core\Exception\ImmutablePropertyException
     *
     * @param string $link
     */
    public function setLink($link)
    {
        throw new ImmutablePropertyException('link', $this);
    }

    /**
     * Gets the publishing date of a job posting
     *
     * @return string
     */
    public function getDatePublishStart()
    {
        return $this->datePublishStart;
    }

    /**
     * Sets the publishing date of a job posting
     * @throws \Core\Exception\ImmutablePropertyException
     *
     * @param $datePublishStart
     * @return string
     */
    public function setDatePublishStart($datePublishStart)
    {
        throw new ImmutablePropertyException('datePublishStart', $this);
    }

    /**
     * Gets the end date for publishing of a job posting
     *
     * @return string
     */
    public function getDatePublishEnd()
    {
        return $this->datePublishStart;
    }

    /**
     * Sets the publishing date of a job posting
     * @throws \Core\Exception\ImmutablePropertyException
     *
     * @param $datePublishEnd
     * @return string
     */
    public function setDatePublishEnd($datePublishEnd)
    {
        throw new ImmutablePropertyException('datePublishEnd', $this);
    }

    /**
     * Gets the title of a job posting
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the title of a job posting
     * @throws \Core\Exception\ImmutablePropertyException
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        throw new ImmutablePropertyException('title', $this);
    }

    /**
     * Gets the organisation name, which offers the job posting
     *
     * @deprecated
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Sets the organisation name, which offers a job posting
     * @throws \Core\Exception\ImmutablePropertyException
     *
     * @deprecated
     * @param string $company
     * @return JobInterface $job
     */
    public function setCompany($company)
    {
        throw new ImmutablePropertyException('company', $this);
    }

    /**
     * Gets the organisation, which offers the job posting
     *
     * @return OrganizationInterface
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * Sets the organization, which offers the job
     * @throws \Core\Exception\ImmutablePropertyException
     *
     * @param OrganizationInterface $organization
     * @return JobInterface
     */
    public function setOrganization(OrganizationInterface $organization = null)
    {
        throw new ImmutablePropertyException('organization', $this);
    }

    /**
     * Sets the contact email of a job posting
     * @throws \Core\Exception\ImmutablePropertyException
     *
     * @param string $email
     * @return JobInterface $job
     */
    public function setContactEmail($email)
    {
        throw new ImmutablePropertyException('contactEmail', $this);
    }

    /**
     * Gets the contact email a job posting
     *
     * @return string
     */
    public function getContactEmail()
    {
        return $this->contactEmail;
    }

    /**
     * Sets the user, who owns a job posting
     * @throws \Core\Exception\ImmutablePropertyException
     *
     * @param UserInterface $user
     * @return JobInterface $job
     */
    public function setUser(UserInterface $user)
    {
        throw new ImmutablePropertyException('userInterface', $this);
    }

    /**
     * Gets the user, who owns a job posting
     *
     * @return UserInterface $user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Gets the link to the application form
     *
     * @return String
     */
    public function getUriApply()
    {
        return $this->uriApply;
    }

    /**
     * Sets the Link to the application form
     * @throws \Core\Exception\ImmutablePropertyException
     *
     * @param String $uriApply
     * @return \Jobs\Entity\Job
     */
    public function setUriApply($uriApply)
    {
        throw new ImmutablePropertyException('uriApply', $this);
    }

    /**
     * Gets the URI of the publisher
     *
     * @return String
     */
    public function getUriPublisher()
    {
        return $this->uriPublisher;
    }

    /**
     * Sets the URI of the publisher
     * @throws \Core\Exception\ImmutablePropertyException
     *
     * @param String $uriPublisher
     * @return \Jobs\Entity\Job
     */
    public function setUriPublisher($uriPublisher)
    {
        throw new ImmutablePropertyException('uriPublisher', $this);
    }

    /**
     * Sets the language of a job posting
     * @throws \Core\Exception\ImmutablePropertyException
     *
     * @param string $language
     */
    public function setLanguage($language)
    {
        throw new ImmutablePropertyException('language', $this);
    }

    /**
     * Gets the language of a job posting
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Sets the location of a job posting
     * @throws \Core\Exception\ImmutablePropertyException
     *
     * @param string $location
     */
    public function setLocation($location)
    {
        throw new ImmutablePropertyException('location', $this);
    }

    /**
     * Gets the location of a job posting
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Sets locations of a job posting
     * @throws \Core\Exception\ImmutablePropertyException
     *
     * @param string $locations
     */
    public function setLocations($locations)
    {
        throw new ImmutablePropertyException('locations', $this);
    }

    /**
     * Gets locations of a job posting
     *
     * @return string
     */
    public function getLocations()
    {
        return $this->locations;
    }

    /**
     * Sets applications for a job posting
     * @throws \Core\Exception\ImmutablePropertyException
     *
     * @param Collection $applications
     */
    public function setApplications(Collection $applications)
    {
        throw new ImmutablePropertyException('applications', $this);
    }

    /**
     * Gets applications for a job posting
     *
     * @return Collection $applications
     */
    public function getApplications()
    {
        return $this->applications;
    }

    /**
     * Sets Status of a job posting
     * @throws \Core\Exception\ImmutablePropertyException
     *
     * @param string $status
     */
    public function setStatus($status)
    {
        throw new ImmutablePropertyException('status', $this);
    }

    /**
     * Gets applications for a job posting
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Sets the collection of history entities.
     * @throws \Core\Exception\ImmutablePropertyException
     *
     * @param Collection $history
     * @return JobInterface
     */
    public function setHistory(Collection $history)
    {
        throw new ImmutablePropertyException('status', $this);
    }

    /**
     * Gets the collection of history entities.
     *
     * @return Collection
     */
    public function getHistory()
    {
        return $this->history;
    }

    /**
     * Sets the terms and conditions accepted flag.
     * @throws \Core\Exception\ImmutablePropertyException
     *
     * @param bool $flag
     * @return self
     */
    public function setTermsAccepted($flag)
    {
        throw new ImmutablePropertyException('termsAccepted', $this);
    }

    /**
     * Gets the terms and conditions accepted flag.
     *
     * @return bool
     */
    public function getTermsAccepted()
    {
        return $this->termsAccepted;
    }

    /**
     * Sets a reference for a job posting, used by the
     * organisation offering the job.
     *
     * @throws \Core\Exception\ImmutablePropertyException
     * @param string $reference
     */
    public function setReference($reference)
    {
        throw new ImmutablePropertyException('reference', $this);
    }

    /**
     * Gets a reference for a job posting, used by the
     * organisation offering the job.
     *
     * @return string $reference
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Sets the list of channels where a job opening should be published
     *
     * @throws \Core\Exception\ImmutablePropertyException
     * @param Array $portals
     */
    public function setPortals(array $portals)
    {
        throw new ImmutablePropertyException('portals', $this);
    }

    /**
     * Gets the list of channels where the job opening should be published
     *
     * @return Array
     */
    public function getPortals()
    {
        $this->portals;
    }

    /**
     * Gets the permissions entity.
     *
     * @return PermissionsInterface
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * the permissions must be mutable because of a flaw in the design of an upper class
     *
     * @param PermissionsInterface $permissions
     * @return $this
     */
    public function setPermissions(PermissionsInterface $permissions)
    {
        $this->permissions = $permissions;
        return $this;
    }

    /**
     * Gets the Values of a job template
     *
     * @return TemplateValues
     */
    public function getTemplateValues()
    {
        return $this->templateValues;
    }

    /**
     * @param EntityInterface $templateValues
     * @return $this
     */
    public function setTemplateValues(EntityInterface $templateValues = null)
    {
        $this->templateValues = $templateValues;
        return $this;
    }

    /**
     * Returns the string identifier of the Resource
     *
     * @return null|string
     */
    public function getResourceId()
    {
        return null;
    }
}
