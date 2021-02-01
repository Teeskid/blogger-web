<?php
/**
 * Json Response Model
 * 
 * Requests sent to /api/ return an object of this class
 * 
 * @package Sevida
 * @subpackage Utilities
 */
class Json {
    /**
     * @var array
     */
    public array $feedBack = [];
    /**
     * @var mixed
     */
    public mixed $message = null;
    /**
     * @var bool
     */
    public bool $success = false;
    /**
     * Appends $message with the additional text provided
     * @param string $message The message to be added
     */
    public function addMessage( string $message ) {
        if( ! is_array($this->message) )
            $this->message = [];
        $this->message[] = $message;
    }
    /**
     * Setter for $message
     * @param mixed $message
     */
    public function setMessage( $message ) {
        $this->message = $message;
    }
    /**
     * Sets a boolean feedback to a child of $feedback
     * @param string $index
     * @param bool $feedBack
     */
    public function setFeedBack( string $index, bool $value ) {
        $this->feedBack[$index] = $value;
    }
    /**
     * Set uniform feedbacks for all the fileds provided
     * @uses setFeedBack
     * @param array $indexes The fields to set feedback for
     * @param bool $feedBack [optional] The single feedback to set to all, default is false
     */
    public function setFeedBacks( array $indexes, bool $value = false ) {
        foreach( $indexes as $index )
            $this->feedBack[$index] = $value;
    }
    /**
     * Sets success indeterminately
     * @param bool $success
     */
    public function setSuccess( bool $success = true ) {
        $this->success = $success;
    }
    /**
     * Checks if there is a false value out of the $feedBack items and evaluates the success of
     * the request. An empty feedback implies success
     * @return bool Returns a boolean status of our request
     */
    private function isSuccessful() {
        if( in_array( false, $this->feedBack ) )
            return false;
        return true;
    }
    /**
     * Checks if the $message is empty
     * @return bool Returns true if the there is no message
     */
    public function hasMessage() : bool {
        if( empty($this->message) )
            return false;
        return true;
    }
    /**
     * Sets value of $success as computed by isSuccessful
     * @uses isSuccessful
     */
    public function determineSuccess() {
        $this->success = $this->isSuccessful();
    }
}