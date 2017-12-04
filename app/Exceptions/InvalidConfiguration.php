<?php
namespace App\Exceptions;
use Exception;

class InvalidConfiguration extends Exception
{
    public static function calendarIdNotSpecified()
    {
        return new static('There was no calendar id specified. You must provide a valid calendar id to fetch events for.');
    }
    public static function credentialsJsonDoesNotExist($path)
    {
        return new static("Could not find a credentials file at `{$path}`.");
    }
}