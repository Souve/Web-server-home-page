<?php
	/**
	* Skatek class
    * @author Souvenance Kavunga (skavunga@gmail.com, +243896358335)
    * @link http://skatek.esy.es
    * @uses Forunir les fonctions essentiels pour le fonctionnement 
    * @license  MIT License (http://www.opensource.org/licenses/mit-license.php)
    * @copyright Skatek Corporation 2017
	*/
   
	class Skatek
	{
        private $dbLink;

        public function getDbLink()
        {
            return $this->dbLink;
        }

        public function setDbLink($value='')
        {
            $this->dbLink = $value;
        }
            
        public function getQuery($val = null)
        {
            if ($val == null) return $_GET;
            return (! empty($_GET[$val])) ? $_GET[$val] : false;
        }    
        public function filesQuery($val = null)
	    {
	        if ($val == null) return $_FILES;
	        return (! empty($_FILES[$val])) ? $_FILES[$val] : false;
	    }
	    public function postQuery($val = null, $null = false)
	    {
	        if ($val == null) return $_POST;
                
                if(! empty($_POST[$val])) {
                    return $_POST[$val];
                } 
                if($null) {
                    return 0;
                }
                return false;
	    }

	    public static function getId()
	    {
	    	return $_SESSION['id'];
	    }

	    public function getUsername()
	    {
	    	return $_SESSION['username'];
	    }

	    public function isPostQuery($options = null)
	    {
	    	if (is_array($options))
		    	foreach ($options as $option) if(! isset($_POST[$option])) return false;		    	
		    else
		    	if (! isset($_POST[$options])) return false;
		    
	    	return true;
	    }

	    public function reqMethod($val = null)
	    {
	    	if ($val == null) return $_SERVER['REQUEST_METHOD'];
	    	return strtoupper($val) == $_SERVER['REQUEST_METHOD'] ? true : false;
	    }

	    public function render($template, $values = [])
	    {
	        // if template exists, render it
	        if (file_exists("../templates/$template.php"))
	        {
	            // extract variables into local scope
	            extract($values);

	            // render header
	            require("../templates/header.php");
                    
                    self::renderFlash();

	            require("../templates/sidebar.php");

	            // render template
	            require("../templates/$template.php");

	            // render footer
	            require("../templates/footer.php");
	        }

	        // else err
	        else
	        {
	            trigger_error("Invalid template: $template", E_USER_ERROR);
	        }
            exit;
	    } 

	    /**
	     * Send mail
	     */
	    function sendMail($expediteur, $destination, $message)
	    {
	        // Include php mailer library
	        require_once("libphp-phpmailer/class.phpmailer.php");

	        $mail = new PHPMailer();
	        $mail->IsSMTP();
	        $mail->Host = "smtp.harvard.edu";
	        $mail->SetFrom($expediteur);
	        $mail->AddAddress($destination);
	        $mail->Subject = "Recovery my password";
	        $mail->Body = $message;

	        if ($mail->Send() === false)
	        {
	            apologize("$mail->ErrorInfo");
	        }
	    }
	    public function hash($password = null)
	    {
	    	if ($password == null) $password = $this->generateUnique('xxx');
	    	return crypt(sha1($password), SKATEK_SALT);
	    }

	    public function generateUnique($n = 'med'){
	        if ($n == 'min') { return sprintf('%03x%03x', date('Ym'), mt_rand(0, 65535), mt_rand(0, 65535)); }
	        else if ($n == 'med') { return sprintf('%04x%04x%04x', date('Ym'), mt_rand(0, 65535), mt_rand(0, 65535)); }
                else if ($n == 'strong') { return sprintf('%04x%05x%05x%04', date('Ymd'), mt_rand(0, 65535), mt_rand(0, 65535), date('H:i:s')); }
	        else { return sprintf('%04x%05x%05x', date('Ymd'), mt_rand(0, 65535), mt_rand(0, 65535)); }
	    }
            /**
             * getDate
             * Obtiens la date dans un format bien specifier pour
             * l'application G - INVIT
             * @param type $date
             * @return boolean
             */
            public function getDate($date = null) {
                if ($date == null) $date = date("d-m-Y");
                return date('l, d F Y', strtotime($date));
            }
            
            /**
             * setFlash
             * Enregistre un message pour la notification dans la session
             * @param type $message
             * @param type $type
             * @return type
             */
            public function setFlash($message = null, $type = 'info') {
                return ($_SESSION['flash'][] = ['text' => $message, 'type' => $type]);
            }
            
            /**
             * renderFlash
             * Affiche le message contenu dans la session flash
             * @return boolean
             */
            public static function renderFlash() {
                if (empty($_SESSION['flash'])) return false;
                if (count($_SESSION['flash']) >= 1){
                    foreach ($_SESSION['flash'] as $k => $value) {
                        empty($value['type']) ? $value['type'] = 'info' : '';
                        empty($value['text']) ? $value['text'] = 'Notification' : '';
                        if ($value['type'] == 'info') $icon = 'ti-info';
                        else if ($value['type'] == 'error') {
                            $value['type'] = 'danger';
                    $icon = 'ti-close';
                } else if ($value['type'] == 'success')
                    $icon = 'ti-check';
                else
                    $icon = 'ti-flag';

                        include '../templates/notifications.php';
                        unset($_SESSION['flash'][$k]);
                    }
                }
            }
            
            
            /**
             * getAvatarByTel()
             * Renvoi le lien de l'avatar dans la table des users
             * quand un numero en possede, Sinon, renvoi l'avatar par defaut
             * @param type $numero
             * @return type
             */
            public function getAvatarByTel($numero = null)
            {
                if ($numero == null) {
                    $numero = $this->getUser ($this->getId ())['phone'];
                }
                $row = query("SELECT * FROM users WHERE phone = ?", $numero);
                if(count($row) == 1){
                    $avatar = $row[0]['username'] . '_' . $row[0]['avatar'];
                    if (file_exists(AVATARS_DIR.$avatar)) {
                        return AVATARS_DIR.$avatar;
                    }
                }
                return $this->getDefaultAvatar();
            }
            
            public function getDefaultAvatar() {
                return $this->defaultAvatar;
            }
            
            public function setDefaultAvatar($name) {
                $this->defaultAvatar = $name;
            }          

            public function referer()
            {
                return $_SERVER['HTTP_REFERER'];
            }
            
            public static function validePhoneNumber($phone = null) {
                if (! $phone) return false;
                $phone = self::removeChar($phone);
                $phone = self::removeChar($phone, '-');
                $phone = self::removeChar($phone, '.');
                
                $taille = strlen($phone);
                
                switch ($taille)
                {
                    case 9:
                        $phone = '243'. $phone;
                        break;
                    case 10:
                        if($phone[0] == 0) {
                            $phone = self::removeChar($phone, '0');
                            $phone = '243'. $phone;
                        }
                        break;
                    case 13:
                        if($phone[0] == '+'){
                            $phone = self::removeChar($phone, '+');
                        } else return false;
                        break;
                    case 12:
                            // Valide
                            break;
                    case 14:
                        if($phone[0] == '0' && $phone[1] == '0'){
                            $phone = (int) $phone;
                        } else return false;
                        break;
                    default :
                        return FALSE;
                }
                return $phone;
            }
            
            public static function removeChar($string, $char = ' ') {
                $t = strlen($string); $nr = '';
                for($i = 0; $i < $t; $i++):
                    if ($string[$i] != $char){
                        $nr .= $string[$i];
                    }
                endfor;
                return $nr;
            }

            public function phpMyAdmin()
            {
                // $sk = fopen('http://'.$this->getDbLink(), 'r');
            }
            
	}
    