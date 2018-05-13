<?
    class Ping extends IPSModule {

        CONST DEFAULT_PING_INTERVAL = 5000;
        CONST DEFAULT_PING_TIMEOUT = 1200;

        public function Create() {
            // Never delete this line
            parent::Create();
        
            $this->RegisterPropertyString('IP', '0.0.0.0');
            $this->RegisterPropertyInteger('Interval', self::DEFAULT_PING_INTERVAL);
            $this->RegisterPropertyInteger('Timeout', self::DEFAULT_PING_TIMEOUT);

            $this->RegisterVariableBoolean('Reachable', 'Reachable');
            $this->RegisterVariableBoolean('Active', 'Active');

            $this->RegisterTimer('PingTimer', self::DEFAULT_PING_INTERVAL, 'PING_Single($_IPS[\'TARGET\']);');
            $this->EnableAction('Active');
        }

        public function ApplyChanges() {
            // Never delete this line
            parent::ApplyChanges();

            $activeId = $this->GetIDForIdent('Active');
            $ip = $this->ReadPropertyString('IP');
            if (!$this->IsIp($ip)) {
                SetValue($activeId, false);
                $this->SetStatus(210);
                return;
            }
            
            $this->SetStatus(102);
            $this->SetTimerInterval('PingTimer', $this->ReadPropertyInteger('Interval'));
            SetValue($activeId, true);
        }

        public function Single() {
            $active = GetValueBoolean($this->GetIDForIdent('Active'));
            if (!$active) {
                return;
            }

            $ip = $this->ReadPropertyString('IP');
            $timeout = $this->ReadPropertyInteger('Timeout');
            $result = Sys_Ping($ip, $timeout);
            SetValue($this->GetIDForIdent('Reachable'), $result);
            //IPS_LogMessage('Ping: ' . $ip, $result);
            
            return $result;
        }

        public function IsReachable() {
            return GetValueBoolean($this->GetIDForIdent('Reachable'));
        }

        private function IsIP($ip) {
            $regex = '/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/';
            return $ip !== '0.0.0.0' && preg_match($regex, $ip) === 1;
        }
    }
?>