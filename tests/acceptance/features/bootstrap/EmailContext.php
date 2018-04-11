<?php
/**
 * ownCloud
 *
 * @author Artur Neumann <artur@jankaritech.com>
 * @copyright Copyright (c) 2018, ownCloud GmbH
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License,
 * as published by the Free Software Foundation;
 * either version 3 of the License, or any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>
 *
 */

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use TestHelpers\EmailHelper;

require_once 'bootstrap.php';

/**
 * context file for email relates steps.
 */
class EmailContext implements Context, SnippetAcceptingContext {
	private $mailhogUrl = null;
	
	/**
	 * @return string
	 */
	public function getMailhogUrl() {
		return $this->mailhogUrl;
	}
	
	/**
	 * @Then the email address :address should have received an email with the body containing
	 * 
	 * @param string $address
	 * @param PyStringNode $content
	 * 
	 * @return void
	 */
	public function assertThatEmailContains($address, PyStringNode $content) {
		$emails = EmailHelper::getEmails($this->mailhogUrl);
		$expectedContent = str_replace("\r\n", "\n", $content->getRaw());
		$content = str_replace(
			"\r\n", "\n", 
			quoted_printable_decode($emails->items[0]->Content->Body)
		);
		PHPUnit_Framework_Assert::assertContains(
			$expectedContent, $content
		);
	}

	/**
	 * @BeforeScenario
	 *
	 * @param BeforeScenarioScope $scope
	 *
	 * @return void
	 */
	public function setUpScenario(BeforeScenarioScope $scope) {
		$mailhogHost = getenv('MAILHOG_HOST');
		if ($mailhogHost === false) {
			$mailhogHost = "127.0.0.1";
		}
		$mailhogPort = getenv('MAILHOG_PORT');
		if ($mailhogPort === false) {
			$mailhogPort = "8025";
		}
		$this->mailhogUrl = "http://$mailhogHost:$mailhogPort";
		$this->clearMailHogMessages();
	}

	/**
	 *
	 * @return void
	 */
	protected function clearMailHogMessages() {
		try {
			EmailHelper::deleteAllMessages($this->getMailhogUrl());
		} catch (Exception $e) {
			echo __METHOD__ .
			" could not delete mailhog messages, is mailhog set up?\n" .
			$e->getMessage();
		}
	}
}
