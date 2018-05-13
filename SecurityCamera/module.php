<?
    class SecurityCamera extends IPSModule {

        public function Create() {
            // Never delete this line
            parent::Create();

            $this->RegisterPropertyString('Camera', '');
            $this->RegisterPropertyString('IP', '0.0.0.0');
            $this->RegisterPropertyInteger('Port', 80);
            $this->RegisterPropertyString('User', '');
            $this->RegisterPropertyString('Password', '');
        }

        public function ApplyChanges() {
            // Never delete this line
            parent::ApplyChanges();

            // Check camera name, get its config and save it to buffer
            $camera = $this->ReadPropertyString('Camera');
            if (!empty($camera)) {
                $camerasConfig = $this->GetCamerasConfig();
                $cameraConfig = $camerasConfig[$camera];
                if (isset($cameraConfig['html'])) {
                    $cameraConfig['html'] = htmlentities($cameraConfig['html'], ENT_QUOTES, 'UTF-8');
                }
                $this->SetBuffer('Config', json_encode($cameraConfig));
            }
        }
        
        public function GetConfigurationForm() {
            // Create camera list
            $camerasConfig = $this->GetCamerasConfig();
            $options = [];
            foreach ($camerasConfig as $cameraName => $cameraConfig) {
                $options[] = [
                    'label' => $cameraName,
                    'value' => $cameraName
                ];
            }

            // Inject camera list into selectbox
            $formString = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'form.json');
            $form = json_decode($formString, true);
            foreach ($form['elements'] as $indexElement => $element) {
                if (isset($element['name']) && $element['name'] === 'Camera') {
                    $form['elements'][$indexElement]['options'] = $options;
                }
            }
            
            return json_encode($form);
        }

        public function GoToPosition($postion) {
            $config = $this->GetCameraConfig();
            if (!isset($config['position'])) {
                IPS_LogMessage('Camera', 'Position feature not supported for ' . $this->ReadPropertyString("Camera"));
                return false;
            }
            $url = $this->BuildUrl($config['position'], ['{$position}' => $postion]);
            return file_get_contents($url);
        }

        public function GetVideo() {
            $config = $this->GetCameraConfig();
            if (!isset($config['video'])) {
                IPS_LogMessage('Camera', 'Video feature not supported for ' . $this->ReadPropertyString("Camera"));
                return '';
            }
            return $this->BuildUrl($config['video']);
        }

        public function GetHtml() {
            $config = $this->GetCameraConfig();
            if (!isset($config['html'])) {
                IPS_LogMessage('Camera', 'HTML feature not supported for ' . $this->ReadPropertyString("Camera"));
                return '';
            }

            return $this->BuildUrl($config['html']);
        }

        private function BuildUrl($url, $additionalReplacements = []) {
            $replacements = [
                '{$ip}' => $this->ReadPropertyString('IP'),
                '{$port}' => $this->ReadPropertyInteger('Port'),
                '{$user}' => $this->ReadPropertyString('User'),
                '{$password}' => $this->ReadPropertyString('Password')
            ];
            $mergedReplacements = array_merge($replacements, $additionalReplacements);
            return strtr($url, $mergedReplacements);
        }

        private function GetCamerasConfig() {
            $camerasConfig = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'cameras.json');
            return json_decode($camerasConfig, true);
        }

        private function GetCameraConfig() {
            $config = json_decode($this->GetBuffer('Config'), true);
            if (isset($config['html'])) {
                $config['html'] = html_entity_decode($config['html'], ENT_QUOTES, 'UTF-8');
            }
            return $config;
        }
    }
?>