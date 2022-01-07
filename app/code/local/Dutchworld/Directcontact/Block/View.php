<?php
class Dutchworld_Directcontact_Block_Directcontact extends Mage_Core_Block_Template {
        public function doSomeAction() {
                $message = '<p>Hello from Zopim...</p>';
                echo $message;
                return $message;
        }
        public function renderBox() {
                $message = '<div class="base-mini box">'
                         . '<div class="head"><h4>Profiles</h4></div>'
                         . '<div class="content">'
                         . '<div class="profiles_icon">';
                $elements = Mage::getStoreConfig('contacts/socialsites');
                $links = array();
                foreach($elements as $element => $value) {
                        $tmp = preg_split('/_/', $element);
                        if(count($tmp) === 2) {
                                $links[$tmp[0]]['enabled'] = $value;
                        } else {
                                $links[$tmp[0]]['url'] = $value;
                        }
                }
                foreach($links as $name => $link) {
                        if(!is_array($link)) {
                                continue;
                        }
                        if(array_key_exists('enabled', $link) && $link['enabled'] && array_key_exists('url', $link) && $link['url']) {
                                $message .= "<a target=\"_blank\" href=\"{$link['url']}\" title=\"" . ucfirst($name)  ."\" class=\"{$name}\"></a>";
                        }

                }
                $message .= '<div class="sp"></div>'

                          . '</div><!-- END profiles_icon -->'
                          . '</div>'
                        . '</div>';
                return $message;
        }
}