<?
    class Ping extends IPSModule {
        public function single($ip, $timeout = 1000) {
            return Sys_Ping($ip, $timeout);
        }

        public function multi(array $ipList) {
            $result = [];
            foreach ($ipList as $name => $ip) {
                $result[$name] = $this->single($ip);
            }
            return $result;
        }

        public function updateList($categoryId, $ipList, $profileName) {
            $result = $this->multi($ipList);
            foreach ($result as $name => $status) {

                $varId = @IPS_GetVariableIDByName($name, $categoryId);
                if (!$varId) {
                    $varId = IPS_CreateVariable(0);
                    IPS_SetName($varId, $name);
                    IPS_SetParent($varId, $categoryId);
                    if ($profileName) {
                        IPS_SetVariableCustomProfile($varId, $profileName);
                    }
                }
                $currentValue = GetValueBoolean($varId);
                if ($currentValue != $response) {
                    SetValueBoolean($varId, $response);
                }
                $result[$name] = $response;
            }
            return $result;
        }
    }
?>