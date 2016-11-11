<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013 - 2016 Cross Solution (http://cross-solution.de)
 * @license   MIT
 * @author    weitz@cross-solution.de
 */

namespace Jobs\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Jobs\Entity\JobInterface as Job;

/**
 * View helper to assemble the link to a job opening
 * @method \Core\View\Helper\Params paramsHelper()
 *
 * @author Carsten Bleek <bleek@cross-solution.de>
 * @todo   write test
 */
class JobUrl extends AbstractHelper
{
    /**
     * Default options
     *
     * @var array
     */
    protected $options = [
        'absolute' => false,
        'linkOnly' => false,
        'target' => false,
        'rel' => 'nofollow',
        'showPendingJobs' => false
    ];

    protected $urlHelper;
    protected $paramsHelper;
    protected $serverUrlHelper;

    public function setUrlHelper($helper)
    {
        $this->urlHelper = $helper;
        return $this;
    }

    public function setParamsHelper($helper)
    {
        $this->paramsHelper = $helper;
        return $this;
    }

    public function setServerUrlHelper($helper)
    {
        $this->serverUrlHelper = $helper;
        return $this;
    }

    public function __invoke(Job $jobEntity, $options = [])
    {

        $options= array_merge($this->options, $options);
        $paramsHelper = $this->paramsHelper;
        $urlHelper = $this->urlHelper;
        $serverUrlHelper = $this->serverUrlHelper;
        $isExternalLink = false;

        if (!empty($jobEntity->getLink())) {
            $url = $jobEntity->getLink();
            $isExternalLink = true;
        }elseif($options['showPendingJobs']) {
            $url = $urlHelper(
                'lang/jobs/approval',
                [],
                [
                    'query' => [
                        'id' => $jobEntity->getId()
                    ]
                ], true);

        }else{

            $query = [
                'subscriberUri' => $serverUrlHelper([]) . '/subscriber/' . 1,
                'id' => $jobEntity->getId()
            ];
            $route = 'lang/jobs/view';
            $params = [
                'lang' => $paramsHelper('lang'),
            ];
            if ($paramsHelper('channel')) {
                $params['channel'] = $paramsHelper('channel');
            }
            $url = $urlHelper($route, $params, array('query' => $query));
        }

        if ($options['linkOnly']){
            $result = $url;
            if ($options['absolute'] && !$isExternalLink){
                $result = $serverUrlHelper($url);
            }
        }else{
            $result = sprintf('<a href="%s" rel="%s" %s>%s</a>',
                              $url,
                              $options['rel'],
                              $options['target']?"target=" . $options['target']:"",
                              strip_tags($jobEntity->getTitle()));
        }

        return $result;
    }

    /**
     * @param $options
     */
    public function setOptions($options){
        foreach($options as $key=>$val) {
            if (array_key_exists($this->options,$key)) {
                $this->options[$key]=$val;
            }
        }
    }
}
