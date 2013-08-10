<?php
/**
* RoundCube BruteforceBreaker Plugin
*
* Several consecutive failed logins will ban the IP address for 30 minutes.
*
* @version 1.1
* @author Arthur Hoaro <http://hoa.ro>
* @url http://git.hoa.ro/arthur/rc-plugin-bruteforce-breaker/tree/master/
* @license MIT
*/
class bruteforcebreaker extends rcube_plugin {
    private $rc;
    private $ipbans_file;
    private $data_ban = array('FAILURES'=>array(),'BANS'=>array());

    function init(){
        $this->rc = rcmail::get_instance();
        $this->ipbans_file = $this->home.'/ipbans.php';
        
        $this->add_hook('login_failed', array($this, 'ban_loginFailed'));
        $this->add_hook('login_after', array($this, 'ban_loginOk'));
        $this->add_hook('authenticate', array($this, 'ban_canLogin'));
    }

    function load_ipban() {
        $this->load_config('config.inc.php.dist');
        $this->load_config('config.inc.php');
        if (!is_file($this->ipbans_file)) $this->write_ipban();
        include $this->ipbans_file;
    }

    function write_ipban() {
        file_put_contents($this->ipbans_file, "<?php\n\$this->data_ban=".var_export( $this->data_ban,true ).";\n?>");
    }

    // Signal a failed login. Will ban the IP if too many failures:
    function ban_loginFailed($args) {
        $ip = $_SERVER['REMOTE_ADDR']; 
        if ( $this->isWhitelisted($ip) )
            return $args;
            
        $this->load_ipban();
            
        if (!isset($this->data_ban['FAILURES'][$ip])) 
          $this->data_ban['FAILURES'][$ip] = 0;
        $this->data_ban['FAILURES'][$ip]++;
        if ($this->rc->config->get('bruteforcebreaker_keep_trace', true)) 
            write_log('bruteforcebreaker', sprintf("Login failed for %s. Number of attemps: %d.\n", $ip, $this->data_ban['FAILURES'][$ip]));
         
        if ($this->data_ban['FAILURES'][$ip] > ($this->rc->config->get('bruteforcebreaker_nb_attemps', 5) -1 )) {
            $this->data_ban['BANS'][$ip] = time() + $this->rc->config->get('bruteforcebreaker_duration', 1800);
            if ($this->rc->config->get('bruteforcebreaker_keep_trace', true)) 
                write_log('bruteforcebreaker', sprintf("IP address banned from login - too many attemps (%s)\n", $ip));
        }

        $this->write_ipban();
        return $args;
    }

    // Signals a successful login. Resets failed login counter.
    function ban_loginOk($args) {
        $ip = $_SERVER['REMOTE_ADDR'];            
        $this->load_ipban();
        
        unset($this->data_ban['FAILURES'][$ip]); 
        unset($this->data_ban['BANS'][$ip]);
        
        $this->write_ipban();
        if ($this->rc->config->get('bruteforcebreaker_keep_trace', true)) 
            write_log('bruteforcebreaker', sprintf("Login ok for %s.\n", $ip));
        return $args;
    }

    // Checks if the user CAN login. If 'true', the user can try to login.
    function ban_canLogin($args) {
        $ip=$_SERVER["REMOTE_ADDR"]; 
        if ( $this->isWhitelisted($ip) )
            return $args;
        
        $this->load_ipban();

        if (!empty($this->data_ban['BANS'][$ip]) ) {
            if( $this->data_ban['BANS'][$ip]<=time()) {             
                unset($this->data_ban['FAILURES'][$ip]); 
                unset($this->data_ban['BANS'][$ip]);
                
                $this->write_ipban();
                if ($this->rc->config->get('bruteforcebreaker_keep_trace', true)) 
                    write_log('bruteforcebreaker', sprintf("Ban lifted for %s.\n", $ip));
            }
            else $args['pass'] = '';
        }      
        
        return $args; 
    }
    
    function isWhitelisted($ip) {
        $this->load_ipban();
        return in_array($ip, $this->rc->config->get('bruteforcebreaker_whitelist', array()));
    }
}

?>