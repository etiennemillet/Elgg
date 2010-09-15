<?php

/**
 * ElggExtender
 *
 * @author Curverider Ltd
 * @package Elgg
 * @subpackage Core
 */
abstract class ElggExtender implements
	Exportable,
	Loggable,	// Can events related to this object class be logged
	Iterator,	// Override foreach behaviour
	ArrayAccess // Override for array access
{
	/**
	 * This contains the site's main properties (id, etc)
	 * @var array
	 */
	protected $attributes;

	/**
	 * Get an attribute
	 *
	 * @param string $name
	 * @return mixed
	 */
	protected function get($name) {
		if (isset($this->attributes[$name])) {
			// Sanitise value if necessary
			if ($name=='value') {
				switch ($this->attributes['value_type']) {
					case 'integer' :
						return (int)$this->attributes['value'];

					//case 'tag' :
					//case 'file' :
					case 'text' :
						return ($this->attributes['value']);

					default :
						throw new InstallationException(sprintf(elgg_echo('InstallationException:TypeNotSupported'), $this->attributes['value_type']));
				}
			}

			return $this->attributes[$name];
		}
		return null;
	}

	/**
	 * Set an attribute
	 *
	 * @param string $name
	 * @param mixed $value
	 * @param string $value_type
	 * @return boolean
	 */
	protected function set($name, $value, $value_type = "") {
		$this->attributes[$name] = $value;
		if ($name == 'value') {
			$this->attributes['value_type'] = detect_extender_valuetype($value, $value_type);
		}

		return true;
	}

	/**
	 * Return the owner of this annotation.
	 *
	 * @return mixed
	 */
	public function getOwner() {
		return $this->owner_guid;
	}

	/**
	 * Return the owner entity
	 *
	 * @return mixed
	 * @since 1.7.0
	 */
	public function getOwnerEntity() {
		return get_user($this->owner_guid);
	}

	/**
	 * Returns the entity this is attached to
	 *
	 * @return ElggEntity The enttiy
	 */
	public function getEntity() {
		return get_entity($this->entity_guid);
	}

	/**
	 * Save this data to the appropriate database table.
	 */
	abstract public function save();

	/**
	 * Delete this data.
	 */
	abstract public function delete();

	/**
	 * Determines whether or not the specified user can edit this
	 *
	 * @param int $user_guid The GUID of the user (defaults to currently logged in user)
	 * @return true|false
	 */
	public function canEdit($user_guid = 0) {
		return can_edit_extender($this->id,$this->type,$user_guid);
	}

	/**
	 * Return a url for this extender.
	 *
	 * @return string
	 */
	public abstract function getURL();

	// EXPORTABLE INTERFACE ////////////////////////////////////////////////////////////

	/**
	 * Return an array of fields which can be exported.
	 */
	public function getExportableValues() {
		return array(
			'id',
			'entity_guid',
			'name',
			'value',
			'value_type',
			'owner_guid',
			'type',
		);
	}

	/**
	 * Export this object
	 *
	 * @return array
	 */
	public function export() {
		$uuid = get_uuid_from_object($this);

		$meta = new ODDMetadata($uuid, guid_to_uuid($this->entity_guid), $this->attributes['name'], $this->attributes['value'], $this->attributes['type'], guid_to_uuid($this->owner_guid));
		$meta->setAttribute('published', date("r", $this->time_created));

		return $meta;
	}

	// SYSTEM LOG INTERFACE ////////////////////////////////////////////////////////////

	/**
	 * Return an identification for the object for storage in the system log.
	 * This id must be an integer.
	 *
	 * @return int
	 */
	public function getSystemLogID() {
		return $this->id;
	}

	/**
	 * Return the class name of the object.
	 */
	public function getClassName() {
		return get_class($this);
	}

	/**
	 * Return the GUID of the owner of this object.
	 */
	public function getObjectOwnerGUID() {
		return $this->owner_guid;
	}

	/**
	 * Return a type of the object - eg. object, group, user, relationship, metadata, annotation etc
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Return a subtype. For metadata & annotations this is the 'name' and
	 * for relationship this is the relationship type.
	 */
	public function getSubtype() {
		return $this->name;
	}


	// ITERATOR INTERFACE //////////////////////////////////////////////////////////////
	/*
	 * This lets an entity's attributes be displayed using foreach as a normal array.
	 * Example: http://www.sitepoint.com/print/php5-standard-library
	 */

	private $valid = FALSE;

	function rewind() {
		$this->valid = (FALSE !== reset($this->attributes));
	}

	function current() {
		return current($this->attributes);
	}

	function key() {
		return key($this->attributes);
	}

	function next() {
		$this->valid = (FALSE !== next($this->attributes));
	}

	function valid() {
		return $this->valid;
	}

	// ARRAY ACCESS INTERFACE //////////////////////////////////////////////////////////
	/*
	 * This lets an entity's attributes be accessed like an associative array.
	 * Example: http://www.sitepoint.com/print/php5-standard-library
	 */

	function offsetSet($key, $value) {
		if ( array_key_exists($key, $this->attributes) ) {
			$this->attributes[$key] = $value;
		}
	}

	function offsetGet($key) {
		if ( array_key_exists($key, $this->attributes) ) {
			return $this->attributes[$key];
		}
	}

	function offsetUnset($key) {
		if ( array_key_exists($key, $this->attributes) ) {
			// Full unsetting is dangerious for our objects
			$this->attributes[$key] = "";
		}
	}

	function offsetExists($offset) {
		return array_key_exists($offset, $this->attributes);
	}
}