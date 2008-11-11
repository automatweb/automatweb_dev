<?php

class aw_resource
{
	protected $data; // any type, actual resource data

	// metainformation
	protected $last_modified; // unix timestamp

	public function __construct()
	{
		$this->last_modified = time();
	}

	/**
	@attrib api=1 params=pos
	@returns mixed
		Raw data as it was set by applications executed.
	**/
	public function data()
	{
		return $this->data;
	}

	/**
	@attrib api=1 params=pos
	@param data required type=mixed
	@returns void
	@comment
		Sets resource data.
	**/
	public function set_data($data)
	{
		$this->data = $data;
	}

	/**
	@attrib api=1 params=pos
	@returns unixtimestamp
		When data was last modified
	**/
	public function last_modified()
	{
		return $this->last_modified;
	}

	/**
	@attrib api=1 params=pos
	@param time required type=unixtimestamp
	@returns void
	@comment
		Updates info about when data was last modified if $time is later than current value.
	**/
	public function set_last_modified($time)
	{
		$this->last_modified = max($this->last_modified, $time);
	}
}

?>
