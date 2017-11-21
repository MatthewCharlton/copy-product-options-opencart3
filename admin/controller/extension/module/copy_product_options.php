<?php

class ControllerExtensionModuleCopyProductOptions extends Controller {

    private $error = array();

    public function index() {
        $this->load->language('extension/module/copy_product_options');

        $this->document->setTitle($this->language->get('heading_title1'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            
            $this->copyOptions($this->request->post);

            $data['success'] = $this->language->get('text_success');

            //$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['entry_product'] = $this->language->get('entry_product');
        $data['entry_product_from'] = $this->language->get('entry_product_from');
        $data['entry_product_to'] = $this->language->get('entry_product_to');
        $data['entry_product'] = $this->language->get('entry_product');
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        
        if (isset($this->request->post['product_from'])) {
            $data['product_from'] = $this->request->post['product_from'];
        } else {
            $data['product_from'] = '';
        }
        if (isset($this->request->post['product_from_value'])) {
            $data['product_from_value'] = $this->request->post['product_from_value'];
        } else {
            $data['product_from_value'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/copy_product_options', 'user_token=' . $this->session->data['user_token'], true),
            'separator' => ' :: '
        );
        
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['product_from_value'])) {
            $data['error_product_from_value'] = $this->error['product_from_value'];
        } else {
            $data['error_product_from_value'] = '';
        }

        if (isset($this->error['product_to_value'])) {
            $data['error_product_to_value'] = $this->error['product_to_value'];
        } else {
            $data['error_product_to_value'] = '';
        }
		if (isset($this->request->post['module_copy_product_options_status'])) {
			$data['module_copy_product_options_status'] = $this->request->post['module_copy_product_options_status'];
		} else {
			$data['module_copy_product_options_status'] = $this->config->get('module_copy_product_options_status');
		}
        
        $data['action'] = $this->url->link('extension/module/copy_product_options', 'user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token']  . '&type=module', true);

        $data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

        // $this->template = 'module/copy_product_options.tpl';
//        $this->children = array(
//            'common/header',
//            'common/footer'
//        );

        $this->response->setOutput($this->load->view('extension/module/copy_product_options', $data));
    }

    public function copyOptions($post) {
        $this->language->load('extension/module/copy_product_options');
        if (empty($post['product_from_value'])) {
            return;
        }
        if (empty($post['product_to_value'])) {
            return;
        }
    
        $selcted_options = array();
        $product_from = $post['product_from_value'];
        $product_to = explode(",", $post['product_to_value']);
        $product_options['product_option'] = $this->getOptions($product_from);
        if (isset($post['option_id']) && !empty($post['option_id'])) {
            $delete_old_options = FALSE;
            $perOption = TRUE;
            foreach ($product_options['product_option'] as $p_options) {
                foreach ($post['option_id'] as $p_option) {
                    if ($p_options['option_id'] == $p_option) {
                        $selcted_options['product_option'][] = $p_options;
                    }
                }
            }
            if (!empty($selcted_options)) {
                foreach ($product_to as $product_id) {
                    $this->putOptions($selcted_options, $product_id, $delete_old_options, $perOption);
                }
            }
        } else {
            $delete_old_options = TRUE;
            $perOption = FALSE;
            foreach ($product_to as $product_id) {
                $this->putOptions($product_options, $product_id, $delete_old_options, $perOption);
            }
        }
    }

    public function getOptions($product_id) {
        $this->load->model('catalog/product');
        $product_options = $this->model_catalog_product->getProductOptions($product_id);
        return $product_options;
    }

    public function putOptions($data, $product_id, $delete_old_options, $perOption) {
        $this->load->model('catalog/product');

        if (isset($data['product_option'])) {
            if ($delete_old_options) {
                $this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = '" . $product_id . "'");
                $this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . $product_id . "'");
            }
           
            if ($perOption) {
                foreach ($data['product_option'] as $Options) {
                    $this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = '" . $product_id . "' AND option_id= '" . $Options['option_id'] . "'");
                    $this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . $product_id . "' AND option_id= '" . $Options['option_id'] . "'");
                }
            }

            foreach ($data['product_option'] as $product_option) {
                if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {

                    $this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int) $product_id . "', option_id = '" . (int) $product_option['option_id'] . "', required = '" . (int) $product_option['required'] . "'");

                    $product_option_id = $this->db->getLastId();

                    if (isset($product_option['product_option_value']) && count($product_option['product_option_value']) > 0) {
                        foreach ($product_option['product_option_value'] as $product_option_value) {
                            $this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_id = '" . (int) $product_option_id . "', product_id = '" . (int) $product_id . "', option_id = '" . (int) $product_option['option_id'] . "', option_value_id = '" . (int) $product_option_value['option_value_id'] . "', quantity = '" . (int) $product_option_value['quantity'] . "', subtract = '" . (int) $product_option_value['subtract'] . "', price = '" . (float) $product_option_value['price'] . "', price_prefix = '" . $this->db->escape($product_option_value['price_prefix']) . "', points = '" . (int) $product_option_value['points'] . "', points_prefix = '" . $this->db->escape($product_option_value['points_prefix']) . "', weight = '" . (float) $product_option_value['weight'] . "', weight_prefix = '" . $this->db->escape($product_option_value['weight_prefix']) . "'");
                        }
                    } else {
                        $this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_option_id = '" . $product_option_id . "'");
                    }
                } 
            }
        }
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/copy_product_options')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (empty($this->request->post['product_from_value'])) {
            $this->error['product_from_value'] = $this->language->get('error_product_from');
        }
        if (empty($this->request->post['product_to_value'])) {
            $this->error['product_to_value'] = $this->language->get('error_product_to');
        }

        return !$this->error;
    }

    public function getAProductOptions() {

        $product_id = $this->request->post['product_id'];
        if ($product_id) {
            $options = $this->getOptions($product_id);
//          echo "<pre>";
//           print_r($options);
            if ($options) {
                $html = '<table class="form" style="width:48%; float:left;">';
                $html .='<tr><td style="width:500px;"><b>Select Options to copy:</b></td></tr>';
                $html .='<tr><th>Name</th><th>Type</th></tr>';
                foreach ($options as $option) {
                    $html .='<tr>'
                            . '<td style="width:500px;"><input type="checkbox" name="option_id[]" value="' . $option['option_id'] . '">' . $option['name'] . '</td>
                            <td>' . $option['type'] . '</td>
                          
                        <tr>';
                }
                echo $html .='</table>';
            } else {
                echo "No Options";
            }
        }
    }

}

?>