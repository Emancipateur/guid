<?php

class Ps_bannerOverride extends Ps_Banner
{
    public function renderForm()
    {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->trans('Settings', [], 'Admin.Global'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'file_lang',
                        'label' => $this->trans('Banner image', [], 'Modules.Banner.Admin'),
                        'name' => 'BANNER_IMG',
                        'desc' => $this->trans('Upload an image for your top banner. The recommended dimensions are 1110 x 214px if you are using the default theme.', [], 'Modules.Banner.Admin'),
                        'lang' => true,
                    ],
                    [
                        'type' => 'file_lang',
                        'label' => $this->trans('Banner image mobile', [], 'Modules.Banner.Admin'),
                        'name' => 'BANNER_IMG_MOBILE',
                        'desc' => $this->trans('Upload an image for your top banner. The recommended dimensions are 375 x 375px if you are using the default theme.', [], 'Modules.Banner.Admin'),
                        'lang' => true,
                    ],
                    [
                        'type' => 'text',
                        'lang' => true,
                        'label' => $this->trans('Banner Link', [], 'Modules.Banner.Admin'),
                        'name' => 'BANNER_LINK',
                        'desc' => $this->trans('Enter the link associated to your banner. When clicking on the banner, the link opens in the same window. If no link is entered, it redirects to the homepage.', [], 'Modules.Banner.Admin'),
                    ],
                    [
                        'type' => 'text',
                        'lang' => true,
                        'label' => $this->trans('Banner description', [], 'Modules.Banner.Admin'),
                        'name' => 'BANNER_DESC',
                        'desc' => $this->trans('Please enter a short but meaningful description for the banner.', [], 'Modules.Banner.Admin'),
                    ],
                ],
                'submit' => [
                    'title' => $this->trans('Save', [], 'Admin.Actions'),
                ],
            ],
        ];

        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->default_form_language = $lang->id;
        $helper->module = $this;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitStoreConf';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'uri' => $this->getPathUri(),
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$fields_form]);
    }

    public function getConfigFieldsValues()
    {
        $languages = Language::getLanguages(false);
        $fields = [];

        foreach ($languages as $lang) {
            $fields['BANNER_IMG'][$lang['id_lang']] = Tools::getValue('BANNER_IMG_' . $lang['id_lang'], Configuration::get('BANNER_IMG', $lang['id_lang']));
            $fields['BANNER_IMG_MOBILE'][$lang['id_lang']] = Tools::getValue('BANNER_IMG_MOBILE_' . $lang['id_lang'], Configuration::get('BANNER_IMG_MOBILE', $lang['id_lang']));
            $fields['BANNER_LINK'][$lang['id_lang']] = Tools::getValue('BANNER_LINK_' . $lang['id_lang'], Configuration::get('BANNER_LINK', $lang['id_lang']));
            $fields['BANNER_DESC'][$lang['id_lang']] = Tools::getValue('BANNER_DESC_' . $lang['id_lang'], Configuration::get('BANNER_DESC', $lang['id_lang']));
        }

        return $fields;
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitStoreConf')) {

            $module_path = _PS_MODULE_DIR_ . 'ps_banner';
            $languages = Language::getLanguages(false);
            $values = [];
            $update_images_values = false;

            foreach ($languages as $lang) {
                if (isset($_FILES['BANNER_IMG_' . $lang['id_lang']])
                    && isset($_FILES['BANNER_IMG_' . $lang['id_lang']]['tmp_name'])
                    && !empty($_FILES['BANNER_IMG_' . $lang['id_lang']]['tmp_name'])) {
                    if ($error = ImageManager::validateUpload($_FILES['BANNER_IMG_' . $lang['id_lang']], 4000000)) {
                        return $this->displayError($error);
                    } else {
                        $ext = substr($_FILES['BANNER_IMG_' . $lang['id_lang']]['name'], strrpos($_FILES['BANNER_IMG_' . $lang['id_lang']]['name'], '.') + 1);
                        $file_name = md5($_FILES['BANNER_IMG_' . $lang['id_lang']]['name']) . '.' . $ext;

                        if (!move_uploaded_file($_FILES['BANNER_IMG_' . $lang['id_lang']]['tmp_name'], $module_path . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . $file_name)) {
                            return $this->displayError($this->trans('An error occurred while attempting to upload the file.', [], 'Admin.Notifications.Error'));
                        } else {
                            if (Configuration::hasContext('BANNER_IMG', $lang['id_lang'], Shop::getContext())
                                && Configuration::get('BANNER_IMG', $lang['id_lang']) != $file_name) {
                                @unlink(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . Configuration::get('BANNER_IMG', $lang['id_lang']));
                            }

                            $values['BANNER_IMG'][$lang['id_lang']] = $file_name;
                        }
                    }
                    
                    $update_images_values = true;
                }
                if (isset($_FILES['BANNER_IMG_MOBILE_' . $lang['id_lang']])
                && isset($_FILES['BANNER_IMG_MOBILE_' . $lang['id_lang']]['tmp_name'])
            && !empty($_FILES['BANNER_IMG_MOBILE_' . $lang['id_lang']]['tmp_name'])) {
                if ($error = ImageManager::validateUpload($_FILES['BANNER_IMG_MOBILE_' . $lang['id_lang']], 4000000)) {
                    return $this->displayError($error);
                } else {
                    $ext = substr($_FILES['BANNER_IMG_MOBILE_' . $lang['id_lang']]['name'], strrpos($_FILES['BANNER_IMG_MOBILE_' . $lang['id_lang']]['name'], '.') + 1);
                    $file_name = md5($_FILES['BANNER_IMG_MOBILE_' . $lang['id_lang']]['name']) . '.' . $ext;
                    
                    if (!move_uploaded_file($_FILES['BANNER_IMG_MOBILE_' . $lang['id_lang']]['tmp_name'], $module_path  . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . $file_name)) {
                        return $this->displayError($this->trans('An error occurred while attempting to upload the file.', [], 'Admin.Notifications.Error'));
                    } else {
                        if (Configuration::hasContext('BANNER_IMG_MOBILE', $lang['id_lang'], Shop::getContext())
                            && Configuration::get('BANNER_IMG_MOBILE', $lang['id_lang']) != $file_name) {
                            @unlink(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . Configuration::get('BANNER_IMG_MOBILE', $lang['id_lang']));
                        }

                        $values['BANNER_IMG_MOBILE'][$lang['id_lang']] = $file_name;
                    }
                }

                $update_images_values = true;
            }

                $values['BANNER_LINK'][$lang['id_lang']] = Tools::getValue('BANNER_LINK_' . $lang['id_lang']);
                $values['BANNER_DESC'][$lang['id_lang']] = Tools::getValue('BANNER_DESC_' . $lang['id_lang']);
            }

            if ($update_images_values && isset($values['BANNER_IMG'])) {
                Configuration::updateValue('BANNER_IMG', $values['BANNER_IMG']);
            }
            if ($update_images_values && isset($values['BANNER_IMG_MOBILE'])) {
                Configuration::updateValue('BANNER_IMG_MOBILE', $values['BANNER_IMG_MOBILE']);
            }

            Configuration::updateValue('BANNER_LINK', $values['BANNER_LINK']);
            Configuration::updateValue('BANNER_DESC', $values['BANNER_DESC']);

            $this->_clearCache($this->templateFile);

            return $this->displayConfirmation($this->trans('The settings have been updated.', [], 'Admin.Notifications.Success'));
        }

        return '';
    }


    public function getWidgetVariables($hookName, array $params)
    {
        $imgname = Configuration::get('BANNER_IMG', $this->context->language->id);
        $imgmobilename = Configuration::get('BANNER_IMG_MOBILE', $this->context->language->id);
        $imgDir = _PS_MODULE_DIR_ . $this->name . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . $imgname;

        if ($imgname && file_exists($imgDir)) {
            $sizes = getimagesize($imgDir);

            $this->smarty->assign([
                'banner_img' => $this->context->link->protocol_content . Tools::getMediaServer($imgname) . $this->_path . 'img/' . $imgname,
                'banner_img_mobile' => $this->context->link->protocol_content . Tools::getMediaServer($imgmobilename) . $this->_path . 'img/' . $imgmobilename,
                'banner_width' => $sizes[0],
                'banner_height' => $sizes[1],
            ]);
        }

        $banner_link = Configuration::get('BANNER_LINK', $this->context->language->id);
        if (!$banner_link) {
            $banner_link = $this->context->link->getPageLink('index');
        }

        return [
            'banner_link' => $this->updateUrl($banner_link),
            'banner_desc' => Configuration::get('BANNER_DESC', $this->context->language->id),
        ];
    }

    private function updateUrl($link)
    {
        if (substr($link, 0, 7) !== 'http://' && substr($link, 0, 8) !== 'https://') {
            $link = 'http://' . $link;
        }

        return $link;
    }
}