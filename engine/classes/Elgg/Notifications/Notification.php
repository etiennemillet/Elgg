<?php
/**
 * Notification container
 * 
 * @package    Elgg.Core
 * @subpackage Notifications
 * @since      1.9.0
 */
class Elgg_Notifications_Notification {
	/** @var ElggEntity The entity causing or creating the notification */
	protected $from;

	/** @var ElggUser The user receiving the notification */
	protected $to;

	/** @var string The subject string */
	public $subject;

	/** @var string The body string */
	public $body;

	/** @var string The language of the notification */
	public $language;

	/** @var array Additional parameters */
	public $params;

	/**
	 * Create a notification
	 *
	 * @param ElggEntity $from     The entity sending the notification (usually the site)
	 * @param ElggEntity $to       The entity receiving the notification
	 * @param string     $language The language code for the notification
	 * @param string     $subject  The subject of the notification
	 * @param string     $body     The body of the notification
	 * @param array      $params   Optional array of parameters
	 * @throws InvalidArgumentException
	 */
	public function __construct(ElggEntity $from, ElggEntity $to, $language, $subject, $body, array $params = array()) {
		if (!$from) {
			throw new InvalidArgumentException('$from is not a valid ElggEntity');
		}
		if (!$to) {
			throw new InvalidArgumentException('$to is not a valid ElggEntity');
		}
		$this->from = $from;
		$this->to = $to;
		$this->language = $language;
		$this->subject = $subject;
		$this->body = $body;
		$this->params = $params;
	}

	/**
	 * Get the sender entity
	 *
	 * @return ElggEntity
	 */
	public function getSender() {
		return $this->from;
	}

	/**
	 * Get the sender entity guid
	 *
	 * @return int
	 */
	public function getSenderGUID() {
		return $this->from->guid;
	}

	/**
	 * Get the recipient entity
	 *
	 * @return ElggEntity
	 */
	public function getRecipient() {
		return $this->to;
	}

	/**
	 * Get the recipient entity guid
	 *
	 * @return int
	 */
	public function getRecipientGUID() {
		return $this->to->guid;
	}
}
