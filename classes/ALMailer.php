<?php
/*
    AlieneilA Event Calendar is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if (!class_exists('AlienCalMailer')) {
   	class AlienCalMailer {
		var $to         = "";
		var $subject    = "";
		var $message    = "";
		var $fromName   = "";
		var $fromEmail  = "";
		var $replyEmail = "";
		var $header     = "";
		var $type       = "text/html";
		var $characterSet = "iso-8859-1";
    
    
		function send(){
		        $this->createHeader();
		        if (@mail($this->to,$this->subject,$this->message,$this->header)){
		            return true;
		        } else {
		            return false;
	        	}
		}
    
		    function createHeader(){
		        $from   = "From: $this->fromName <$this->fromEmail>\r\n";
		        $replay = "Reply-To: $this->replyEmail\r\n";    
		        $params = "MIME-Version: 1.0\r\n";
		        $params .= "Content-type: $this->type; charset=$this->characterSet\r\n";
	        
		        $this->header = $from.$replay.$params;
	        	return $this->header;
		}
	}
}
?>