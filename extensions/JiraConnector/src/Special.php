<?php
use MediaWiki\MediaWikiServices;

class SpecialJiraConnector extends SpecialPage {
	
	private $jira_user;
	private $jira_pass;
	private $jira_url;
	private $jira_label;
	private $jira_projectid;
	
	function __construct() {
		parent::__construct( 'JiraConnector' );
		$config = ConfigFactory::getDefaultInstance()->makeConfig( 'JiraConnector' );
		$this->jira_user = $config->get('JiraConnector_Jira_User');
		$this->jira_pass = $config->get('JiraConnector_Jira_Pass');
		$this->jira_url = $config->get('JiraConnector_Jira_URL');
		$this->jira_label = $config->get('JiraConnector_Jira_Label');
		$this->jira_projectid = $config->get('JiraConnector_Jira_ProjectID');
	}

	function execute( $par ) {
		# $par contains everything after / in Special:JiraConnector/bla
		$request = $this->getRequest();
		$output = $this->getOutput();
		$user = $output->getUser();
		$this->setHeaders();
		
		$output->addHTML("
		<h2>Report Bugs & Request Features</h2>
		<p>On this page you can report and review:
		<ul>
		<li>any bugs you or someone else might have found in the software</li>
		<li>any requests for particular features in the software</li>
		</ul>
		</p>");
		# show form
		$this->ShowIssueReportForm($user, $output);
		
		# get Jira issues & show table
		$jira_call = 	$this->jira_url."search?jql=";
		$jira_jql = "project = ".$this->jira_projectid;
		if (!empty($this->jira_label)) $jira_jql .= " And labels = \"".$this->jira_label."\"";
		$jira_call .= urlencode($jira_jql);
		$ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $jira_call);
		curl_setopt($ch, CURLOPT_USERPWD, $this->jira_user . ":" . $this->jira_pass);  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $jira_response = curl_exec($ch);
    curl_close($ch);
		$this->ShowSprintIssueList(json_decode($jira_response), $output);
	}
	
	function PostIssueToJira($formData) {
		$output = $this->getOutput();
		$user = $output->getUser();
		if (isset($formData['IssueType']) && isset($formData['IssueSummary']) && isset($formData['IssueDescription']) && $user->isLoggedIn()) {
			# simple sanitasion
			$formData['IssueSummary'] = strip_tags($formData['IssueSummary']);
			$formData['IssueDescription'] = strip_tags($formData['IssueDescription']);
			# first check if this exact IssueType, IssueSummary & IssueDescription is already in the list
			if ($this->AnyDuplicatesInJira($formData['IssueType'], $formData['IssueSummary'], $formData['IssueDescription'])) {
				return 'This '.$formData['IssueType'].' has already been recorded. Please see the list below.';
			}
			$formData['IssueDescription'] = $formData['IssueDescription'].'
			
			Reported by '.$user->getName();
			if (isset($formData['IssueVersion'])) $formData['IssueDescription'] .= ' using version '.$formData['IssueVersion'];
			$data = array(
				'fields' => array(
					'project' => array(
						'key' => 'MSP'
					),
					'summary' => $formData['IssueSummary'],
					'description' => $formData['IssueDescription'],
					'issuetype' => array(
						'name' => $formData['IssueType']
					)
				)
			);
			if ($formData['IssueType'] == "Task") $data['fields']['customfield_10006'] = 1.0;
			if (!empty($this->jira_label)) $data['fields']['labels'] = array('CommunityReported');
			$headers = array(
			'Accept: application/json',
			'Content-Type: application/json'
			);
			$jira_call = 	$this->jira_url."issue/";
			$ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $jira_call);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_USERPWD, $this->jira_user.":".$this->jira_pass);  
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	    $jira_response = curl_exec($ch);
			$ch_error = curl_error($ch);
	    curl_close($ch);
			if ($ch_error) {
				return $ch_error;
			}
			else {
				if (isset($jira_response->errorMessages)) {
					return $jira_response->errorMessages[0];
				}
				return true;
			}
		}
		return 'Please try again.';
	}
	
	function AnyDuplicatesInJira($IssueType, $IssueSummary, $IssueDescription) {
		$jira_call = 	$this->jira_url."search?jql=";
		$jira_jql = "project = ".$this->jira_projectid;
		if (!empty($this->jira_label)) $jira_jql .= " And labels = \"".$this->jira_label."\"";
		$jira_jql .= " And issuetype = \"".$IssueType."\" And summary ~ \"".$IssueSummary."\" And description ~ \"".$IssueDescription."*\"";
		$jira_call .= urlencode($jira_jql);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $jira_call);
		curl_setopt($ch, CURLOPT_USERPWD, $this->jira_user . ":" . $this->jira_pass);  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$jira_response = json_decode(curl_exec($ch));
		curl_close($ch);
		if (isset($jira_response->errorMessages)) return true;
		elseif ($jira_response->total > 0) return true;
		return false;
	}
	
	function ShowSprintIssueList($jira_response, $output) {
		if (isset($jira_response->errorMessages)) $wikitext = $jira_response->errorMessages[0];
		else {
			if ($jira_response->total === 0) {
				$wikitext = '
				No bugs reports or feature requests on record yet.';
			}
			else {
				$wikitext = '
					{| class="wikitable sortable"
					!ID!!Type!!Dated!!Title/Summary!!Description!!Status!!Version fixed/added';
				foreach ($jira_response->issues as $issue) {
					if (!empty($issue->fields->fixVersions)) $fixVersions = $issue->fields->fixVersions[0]->name;
					else $fixVersions = 'to be assigned';
					$wikitext .= '
					|-
					|'.$issue->key.'
					|'.$issue->fields->issuetype->name.'
					|'.substr($issue->fields->created, 0, 10).'
					|'.$issue->fields->summary.'
					|'.$issue->fields->description.'
					|'.$issue->fields->status->statusCategory->name.'
					|'.$fixVersions;
				}
				$wikitext .= '
					|}';
			}
		} 
		$output->addWikiTextAsInterface( $wikitext );
	}
	
	function ShowIssueReportForm($user, $output) {
		if ($user->isLoggedIn()) {
				// get all versions
				$versions = $this->GetAllProjectVersions();
				// formDescriptor Array to tell HTMLForm what to build
		    $formDescriptor = [
		        'IssueType' => [
								'section' => 'jiraformsection1',
								'type' => 'select',
				        'options' => [
				            'Bug report' => 'Bug', 
				            'Feature request' => 'Task' 
				        ],
		            'label' => 'Type', // Label of the field
								'required' => true
		        ],
						'IssueSummary' => [
								'section' => 'jiraformsection1',
								'type' => 'text',
		            'label' => 'Title / Summary', // Label of the field
								'required' => true
		        ],
						'IssueDescription' => [
								'section' => 'jiraformsection1',
								'type' => 'textarea',
								'rows' => 5,
		            'label' => 'Description', // Label of the field
								'required' => true
		        ]
		    ];
				if (!empty($versions)) {
					$formDescriptor['IssueVersion'] = 
						[
								'section' => 'jiraformsection1',
								'type' => 'select',
								'options' => $versions,
								'label' => 'Currently using version', // Label of the field
								'required' => true
						];
				}
		    // Build the HTMLForm object
		    $htmlForm = HTMLForm::factory( 'table', $formDescriptor, $this->getContext() );
		    // Text to display in submit button
		    $htmlForm->setSubmitText( 'Submit' );
				// where to post the form values
				$htmlForm->setSubmitCallback( [ $this, 'PostIssueToJira' ] );
		    $htmlForm->show(); // Display the form
		}
		else {
			$output->addWikiTextAsInterface('
			
			To submit a bug report or feature request, please [[Special:UserLogin|log in]] first.
			');
		}
	}
	
	function GetAllProjectVersions() {
		$jira_call = 	$this->jira_url."project/".	$this->jira_projectid."/versions";
		$ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $jira_call);
		curl_setopt($ch, CURLOPT_USERPWD, $this->jira_user . ":" . $this->jira_pass);  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $jira_response = json_decode(curl_exec($ch));
    curl_close($ch);
		$versions = array();
		foreach ($jira_response as $version) {
			if ($version->released)	$versions[$version->name] = $version->name;
		}
		return $versions;
	}
	
}
