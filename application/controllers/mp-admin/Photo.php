<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Photo extends CI_Controller {
    var $data;
    var $user_id;
    var $link = 'mp-admin/photo';
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library(array('ion_auth', 'form_validation'));
        $this->load->helper(array('url', 'language','openssl_class'));
        //models connection back-end
        $this->load->model('ad-min/M_settings', 'settings');
        $this->load->model('ad-min/M_logactivity', 'logactivity');
        $this->load->model('ad-min/M_menu', 'menu');
        $this->load->model('ad-min/M_user', 'user');
        //end models connection back-end
        $this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));
        $this->lang->load('auth');
    }
    // redirect if needed, otherwise display the menu list

    public function data_list() {
      if (!$this->input->is_ajax_request()) show_404();
        // -- start -- //
        $data = array();
        //-- setup datatables --//
        $start = $this->input->post('start');
        $length = $this->input->post('length');

        $orderby = $this->input->post('order[0][column]');
        $ordertype = $this->input->post('order[0][dir]');

        $search = $this->input->post('search[value]');
        //-- end --//

        /* START insert update delete view akses */
        $user_id = $this->session->userdata('user_id');
        $akses['akses'] = $this->user->get_access($user_id, $this->link); //get access for menu content settings
        /* STOP insert update delete view akses */

        $edit = $akses['akses'][0]['update'];
        $delete = $akses['akses'][0]['delete'];

        $value = $this->settings->fetchPhoto($start, $length, $orderby, $ordertype, $search);
        // -- datatables --//

        foreach ($value['data'] as $key) {
          $start ++;
          $row = array();
          $row[] = $start;

          $row[] = $key['title'];
          $row[] = $key['text'];
          $row[] = $key['image'];
          $row[] = $key['date_created'];

          if ($edit == 1) {
            $edit_row = '<button class="btn btn-outline btn-primary" type="button" href="javascript:void()" title="edit" onclick="bttn_editing_photo(' . "'" . encrypt_decrypt('encrypt', $key['id']) . "'" . ')"><i class="ti-pencil-alt"></i></button>';
          } else {
            $edit_row = '';
          }
          if ($delete == 1) {
            $delete_row = '<button class="btn btn-outline btn-danger" type="button"  href="javascript:void()" title="delete" onclick="bttn_delete_photo(' . "'" . encrypt_decrypt('encrypt', $key['id']) . "'" . ')"><i class="ti-trash"></i></button>';
          } else {
            $delete_row = '';
          }
          $row[] = $edit_row . ' ' . $delete_row;
          $data[] = $row;
        }

        $output = array(
          "draw" => $this->input->post('draw'),
          "recordsTotal" => $value['total_all'],
          "recordsFiltered" => $value['total_filter'],
          "data" => $data,
        );
        echo json_encode($output, JSON_PRETTY_PRINT);
        // -- end of datatables --//
    }

    public function index() {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('mp-admin/app/login', 'refresh');
        } elseif (!$this->ion_auth->is_admin()) // remove this elseif if you want to enable this for non-admins
        {
            // redirect them to the home page because they must be an administrator to view this
            return show_error('You must be an administrator to view this page.');
        } else {
            $this->data['title_site'] = $this->settings->get_title_site();
            $user_id = $this->session->userdata('user_id');
            //start sidebar menu
            $this->data['menu_data'] = $this->settings->get_menu($user_id);
            $this->data['submenu_data'] = $this->settings->get_sub_menu($user_id);
            /* START insert update delete view access */
            $this->data['akses'] = $this->user->get_access($user_id, $this->link);
            /* STOP insert update delete view access */
            $this->data['sub_header'] = "kegiatan";
            $this->data['content_header'] = "foto";
            // logo header
            $this->data['ic_logo'] = $this->settings->get_logo_header();
            $this->data['favicon_logo'] = $this->settings->get_favicon_picture();
            //end page header
            $this->data['parent_data'] = $this->settings->get_all();
			 // -- pagination settings -- \\
              $page=$this->input->get('per_page');
              $batas=10; //jlh data yang ditampilkan per halaman
              if(!$page):     //jika page bernilai kosong maka batas akhirna akan di set 0
                 $offset = 0;
              else:
                 $offset = $page; // jika tidak kosong maka nilai batas akhir nya akan diset nilai page terakhir
              endif;

              $config['page_query_string'] = TRUE; //mengaktifkan pengambilan method get pada url default
              $config['base_url'] = base_url().'mp-admin/photo?';   //url yang muncul ketika tombol pada paging diklik
              $config['total_rows'] = $this->settings->count_photo_array(); // jlh total article
              $config['per_page'] = $batas; //batas sesuai dengan variabel batas
              $config['uri_segment'] = $page; //merupakan posisi pagination dalam url pada kesempatan ini saya menggunakan method get untuk menentukan posisi pada url yaitu per_page

              $config['full_tag_open'] = '<ul class="pagination">';
              $config['full_tag_close'] = '</ul>';
              $config['first_link'] = '&laquo; First';
              $config['first_tag_open'] = '<li class="prev page">';
              $config['first_tag_close'] = '</li>';

              $config['last_link'] = 'Last &raquo;';
              $config['last_tag_open'] = '<li class="next page">';
              $config['last_tag_close'] = '</li>';

              $config['next_link'] = 'Next &rarr;';
              $config['next_tag_open'] = '<li class="next page">';
              $config['next_tag_close'] = '</li>';

              $config['prev_link'] = '&larr; Prev';
              $config['prev_tag_open'] = '<li class="prev page">';
              $config['prev_tag_close'] = '</li>';

              $config['cur_tag_open'] = '<li class="current"><a href="">';
              $config['cur_tag_close'] = '</a></li>';

              $config['num_tag_open'] = '<li class="page">';
              $config['num_tag_close'] = '</li>';
              $this->pagination->initialize($config);

              $this->data['total_rows'] = "";
		      $this->data['search'] = "";
              $this->data['student_list'] = $this->settings->get_alumni();
              $this->data['pagination']=$this->pagination->create_links();

              $this->data['jlhpage']=$page;
              // end pagination
              // get article list
              $this->data['photo_data'] = $this->settings->get_photo_array($batas,$offset);

            //print_r($this->data['photo_data']);die;
            //content view
            $this->data['content'] = $this->load->view('ad-min/content/v_photo', $this->data, true);
            //end content view
            //main content
            $this->load->view('ad-min/main/v_main', $this->data);
            //end main content
        }
    }
	public function search() {
		if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('ad-min/c_alpha/login', 'refresh');
        } elseif (!$this->ion_auth->is_admin()) // remove this elseif if you want to enable this for non-admins
        {
            // redirect them to the home page because they must be an administrator to view this
            return show_error('you must be an administrator to view this page.');
        } else {
            $user_id = $this->session->userdata('user_id');
            //start sidebar menu
            $this->data['title_site'] = $this->settings->get_title_site();
            $this->data['menu_data'] = $this->settings->get_menu($user_id);
            $this->data['submenu_data'] = $this->settings->get_sub_menu($user_id);
            /* START insert update delete view access */
            $this->data['akses'] = $this->user->get_access($user_id, $this->link);
            /* STOP insert update delete view access */
            $this->data['content_header'] = $this->uri->segment(2);
            // logo header
            $this->data['ic_logo'] = $this->settings->get_logo_header();
            $this->data['favicon_logo'] = $this->settings->get_favicon_picture();
            //end page header
            $this->data['parent_data'] = $this->settings->get_all();

          // -- pagination settings -- \\
          $key= $this->input->get('key'); //method get key
          $page=$this->input->get('per_page');  //method get per_page

          $search=array(
              'a.title'=> $key,
              'a.text'=> $key
          ); //array pencarian yang akan dibawa ke model

          $batas=10; //jlh data yang ditampilkan per halaman
          if(!$page):     //jika page bernilai kosong maka batas akhirna akan di set 0
             $offset = 0;
          else:
             $offset = $page; // jika tidak kosong maka nilai batas akhir nya akan diset nilai page terakhir
          endif;

          $config['page_query_string'] = TRUE; //mengaktifkan pengambilan method get pada url default
          $config['base_url'] = base_url().'mp-admin/photo?key='.$key;   //url yang muncul ketika tombol pada paging diklik
          $config['total_rows'] = $this->settings->count_photo_array_search($search); // jlh total barang
          $config['per_page'] = $batas; //batas sesuai dengan variabel batas

          $config['uri_segment'] = $page; //merupakan posisi pagination dalam url pada kesempatan ini saya menggunakan method get untuk menentukan posisi pada url yaitu per_page

          $config['full_tag_open'] = '<ul class="pagination">';
          $config['full_tag_close'] = '</ul>';
          $config['first_link'] = '&laquo; First';
          $config['first_tag_open'] = '<li class="prev page">';
          $config['first_tag_close'] = '</li>';

          $config['last_link'] = 'Last &raquo;';
          $config['last_tag_open'] = '<li class="next page">';
          $config['last_tag_close'] = '</li>';

          $config['next_link'] = 'Next &rarr;';
          $config['next_tag_open'] = '<li class="next page">';
          $config['next_tag_close'] = '</li>';

          $config['prev_link'] = '&larr; Prev';
          $config['prev_tag_open'] = '<li class="prev page">';
          $config['prev_tag_close'] = '</li>';

          $config['cur_tag_open'] = '<li class="current"><a href="">';
          $config['cur_tag_close'] = '</a></li>';

          $config['num_tag_open'] = '<li class="page">';
          $config['num_tag_close'] = '</li>';

          $this->pagination->initialize($config);

          $this->data['jlhpage']=$page;
          // pagination

		  $this->data['total_rows'] = $this->settings->count_photo_array_search($search);
		  $this->data['search'] = $key;
          $this->data['photo_data'] = $this->settings->get_photo_array($batas,$offset,$search);
          // pagination
          $this->data['pagination'] = $this->pagination->create_links();
          //end pagination --//
		  //content view
		  $this->data['content'] = $this->load->view('ad-min/content/v_photo', $this->data, true);
		  //end content view
		  //main content
		  $this->load->view('ad-min/main/v_main', $this->data);
		  //end main content
        }
	}
	public function alumni_id(){
        $search = strip_tags(trim($_GET['q']));
        $array = $this->settings->get_alumni_id($search);
        //print_r($array);die;
        if(count($array) > 0){
            foreach ($array as $key => $value) {
                $data[] = array('id' => $value->id, 'name' => $value->firstname.' '.$value->lastname);
            }
        } else {
            $data[] = array('id' => 0, 'name' => 'nama alumni tidak di temukan');
        }

        echo json_encode($data);
    }
    public function edit($id) {
      $id = $this->input->post('id');
      $data = $this->settings->get_id_photo(encrypt_decrypt('decrypt', $id));
      echo json_encode($data);
    }
    public function add() {

      $this->validate_add();	// validasi file

      if(empty($_FILES['file_image']['tmp_name'])) {
            $title = count($this->input->post('title'));
            for($i = 0; $i < $title; $i++) {

                if($_POST['type_photo'][$i]=="activities"){
                    $data = array (
                        'picture' => "",
                        'date_created' => date("Y-m-d"),
                        'title' => trim($_POST['title'][$i]),
                        'position' => trim($_POST['position'][$i]),
                        'alumni_id' => trim($_POST['student'][$i]),
                        'user_created' => $this->session->userdata('user_id')
                    );
                    $insert = $this->settings->save_photo_activities($data);
                } else {
                    $data = array (
                        'image' => "",
                        'date_created' => date("Y-m-d"),
                        'title' => trim($_POST['title'][$i]),
                        'position' => trim($_POST['position'][$i]),
                        'text' => trim($_POST['text'][$i]),
                        'user_created' => $this->session->userdata('user_id')
                    );
                    $insert = $this->settings->save_photo($data);
                }

            }
            $data_log = array('user_id' => $this->session->userdata('user_id'), 'user_name' => $this->session->userdata('first_name'), 'link' => 'c_photo', 'class' => 'ti-plus', 'activity' => 'adding photo', "ip" => $this->session->userdata('ip'), "countries_sess" => $this->session->userdata('countries_sess'));
            $this->logactivity->insertlog($data_log);

        echo json_encode(array("status" => TRUE));

        } else {
        // cek berapa file yang akan di upload;
		$number_of_files = sizeof($_FILES['file_image']['tmp_name']);
		$title = count($this->input->post('title'));
    $files = $_FILES['file_image'];
		$errors = array();
        if(isset($_FILES['file_image'])){
             for($i = 0; $i < $title; $i++) {
             $this->image_path = realpath(APPPATH.'../image/event');
             $this->image_path_url = base_url().'image/event';
                $config = array(
                    'allowed_types' => 'jpg|gif|GIF|jpeg|png|JPG|JPEG|PNG',
                    'upload_path' 	=> $this->image_path,
                    'encrypt_name' 	=> TRUE
                );
                if(!empty($files['name'][$i])) {
                    $_FILES['file_image']['name'] 		= $files['name'][$i];
                    $_FILES['file_image']['type'] 		= $files['type'][$i];
                    $_FILES['file_image']['tmp_name'] 	= $files['tmp_name'][$i];
                    $_FILES['file_image']['error'] 		= $files['error'][$i];
                    $_FILES['file_image']['size'] 		= $files['size'][$i];
                        $this->load->library('upload');
                        $this->upload->initialize($config);
                        if ($this->upload->do_upload('file_image')) {
                            $upload_data = $this->upload->data();
                            $image_config["image_library"] = "gd2";
                            $image_config["source_image"] = $this->image_path . '/' . $upload_data['file_name'];
                            $image_config['create_thumb'] = FALSE;
                            $image_config['maintain_ratio'] = TRUE;
                            $image_config['new_image'] = $this->image_path . '/' . $upload_data['file_name'];
                            $image_config['quality'] = "100%";
                            $image_config['width'] = 940;
                            $image_config['height'] = 480;
                            $dim = (intval($upload_data["image_width"]) / intval($upload_data["image_height"])) - ($image_config['width'] / $image_config['height']);
                            $image_config['master_dim'] = ($dim > 0)? "height" : "width";
                            $this->load->library('image_lib');
                            $this->image_lib->initialize($image_config);
                            $this->image_lib->resize();

                            if($_POST['type_photo'][$i]=="activities"){
                                $data = array (
                                    'picture' => $upload_data["file_name"],
                                    'date_created' => date("Y-m-d"),
                                    'title' => $_POST['title'][$i],
                                    'alumni_id' => $_POST['student'][$i],
                                    'user_created' => $this->session->userdata('user_id')
                                );
                                $insert = $this->settings->save_photo_activities($data);
                            } else {
                                $data = array (
                                    'image' => $upload_data["file_name"],
                                    'date_created' => date("Y-m-d"),
                                    'title' => $_POST['title'][$i],
                                    'text' => $_POST['text'][$i],
                                    'position' => trim($_POST['position'][$i]),
                                    'user_created' => $this->session->userdata('user_id')
                                );
                                $insert = $this->settings->save_photo($data);
                            }


                        } else {
                            $data['upload_errors'][$i] = $this->upload->display_errors();
                        }
                    } else {
                        if($_POST['type_photo'][$i]=="activities"){
                            $data = array (
                                'picture' => $upload_data["file_name"],
                                'date_created' => date("Y-m-d"),
                                'title' => $_POST['title'][$i],
                                'position' => trim($_POST['position'][$i]),
                                'alumni_id' => $_POST['student'][$i],
                                'user_created' => $this->session->userdata('user_id')
                            );
                            $insert = $this->settings->save_photo_activities($data);
                        } else {
                            $data = array (
                                'image' => "",
                                'date_created' => date("Y-m-d"),
                                'title' => $_POST['title'][$i],
                                'position' => trim($_POST['position'][$i]),
                                'text' => $_POST['text'][$i],
                                'user_created' => $this->session->userdata('user_id')
                            );
                            $insert = $this->settings->save_photo($data);
                        }
                    }
                }
            }
            $data_log = array('user_id' => $this->session->userdata('user_id'), 'user_name' => $this->session->userdata('first_name'), 'link' => 'c_photo', 'class' => 'ti-plus', 'activity' => 'adding photo', "ip" => $this->session->userdata('ip'), "countries_sess" => $this->session->userdata('countries_sess'));
            $this->logactivity->insertlog($data_log);

            echo json_encode(array("status" => TRUE));
        }

    }
    public function update() {
      $this->validate_edit();
      $data = $this->data_save('update');
      if ($this->form_validation->run() == TRUE) {
          $update = $this->settings->update_photo(array('id' => encrypt_decrypt('decrypt', $this->input->post('id'))), $data);
          $data_log = array('user_id' => $this->session->userdata('user_id'), 'user_name' => $this->session->userdata('first_name'), 'link' => 'c_photo', 'class' => 'ti-plus', 'activity' => 'update photo', "ip" => $this->session->userdata('ip'), "countries_sess" => $this->session->userdata('countries_sess'));
          $this->logactivity->insertlog($data_log);

      }
      echo json_encode(array("status" => TRUE));
    }
    public function delete($id) {
      $data = array('active' => 0, 'date_deleted' => date('Y-m-d'), 'user_deleted' => $this->session->userdata('id'));
      $data_log = array('user_id' => $this->session->userdata('user_id'), 'user_name' => $this->session->userdata('first_name'), 'link' => 'c_photo', 'class' => 'ti-trash', 'activity' => 'delete photo', "ip" => $this->session->userdata('ip'), "countries_sess" => $this->session->userdata('countries_sess'));

      $this->image_path = realpath(APPPATH . '../image/event');

      if ($this->settings->check_photo_img(encrypt_decrypt('decrypt', $id)) != '' && file_exists($this->image_path . '/' . $this->settings->check_photo_img(encrypt_decrypt('decrypt', $id)))) unlink($this->image_path . '/' . $this->settings->check_photo_img(encrypt_decrypt('decrypt', $id)));

      $this->settings->delete_photo(encrypt_decrypt('decrypt', $id));

      $this->logactivity->insertlog($data_log);
      echo json_encode(array("status" => TRUE));
    }
    public function data_save($type) {
      $user_id = $this->session->userdata('user_id');
      if ($type == 'add') {
          $field_user = 'user_created';
          $field_date = 'date_created';
      } else {
          $field_user = 'user_modified';
          $field_date = 'date_modified';
      }
      $date = date("Y-m-d");

      /* --- images config --- */
     if (empty($_FILES['file_image'])) {

     } else {

         $this->image_path = realpath(APPPATH . '../image/event');
         $this->image_path_url = base_url() . 'image/event';

         if(!empty($this->input->post('id'))) {
            if ($this->settings->check_photo_img($this->input->post('id')) != '' && file_exists($this->image_path . '/' . $this->settings->check_photo_img($this->input->post('id')))) unlink($this->image_path . '/' . $this->settings->check_photo_img($this->input->post('id')));
         }

         $config = array('allowed_types' => 'jpg|jpeg|gif|png|JPG|JPEG|GIF|PNG', 'upload_path' => $this->image_path, 'overwrite' => TRUE, 'encrypt_name' => TRUE);

         $this->load->library('upload', $config);
         $this->upload->do_upload('file_image');
         // resize image
         $upload_data = $this->upload->data();
         $image_config["image_library"] = "gd2";
         $image_config["source_image"] = $this->image_path . '/' . $upload_data['file_name'];
         $image_config['create_thumb'] = TRUE;
         $image_config['maintain_ratio'] = FALSE;
         $image_config['new_image'] = $this->image_path . '/' . $upload_data['file_name'];
         $image_config['quality'] = "100%";
         $image_config['width'] = 320;
         $image_config['height'] = 480;
         $this->load->library('image_lib');
         $this->image_lib->initialize($image_config);
         $this->image_lib->resize();
         $this->image_lib->clear();
     }

     if (empty($upload_data['file_name'])){ $image=''; } else { $image = $upload_data['file_name']; }
     if ($image!=='') {
         $data = array (
             'img' => $image,
             $field_date => $date,
             'title' => $this->input->post('title_edit'),
             'position' => $this->input->post('position_edit'),
             'text' => $this->input->post('text_edit'),
             $field_user => $user_id
         );
     } else {
        $data = array (
            //'img' => $upload_data["file_name"],
             'title' => $this->input->post('title_edit'),
             'position' => $this->input->post('position_edit'),
             'text' => $this->input->post('text_edit'),
             $field_date => $date,
             $field_user => $user_id
         );
     }
     return $data;
    }
    public function generate_thumb($filename) {
    // if path is not given use default path //
      $this->image_path = realpath(APPPATH . '../image/event');
      $config_thumb['image_library'] = 'gd2';
      $config_thumb['source_image'] = $this->image_path . '/' . $filename;
      $config_thumb['create_thumb'] = TRUE;
      $config_thumb['maintain_ratio'] = FALSE;
      $config_thumb['quality'] = "100%";
      $config_thumb['width'] = 370;
      $config_thumb['height'] = 350;

      $this->load->library('image_lib');
      $this->image_lib->initialize($config_thumb);


      if (!$this->image_lib->resize()) {
          echo $this->image_lib->display_errors();
          return FALSE;
      }
      $this->image_lib->clear();
      // get file extension //
      preg_match('/(?<extension>\.\w+)$/im', $filename, $matches);
      $extension = $matches['extension'];
      // thumbnail //
      $thumbnail = preg_replace('/(\.\w+)$/im', '', $filename) . '_thumb' . $extension;
      return $thumbnail;
    }
    public function validate_add(){
      $data = array();
      $data['error_string'] = array();
      $data['inputerror'] = array();
      $data['file_id'] = array();
      $data['error_file'] = array();
      $data['status'] = TRUE;

         $title = $this->input->post('title');
         if(!empty($title))
            {
                foreach($title as $id => $value)
                {
                    $this->form_validation->set_rules('title['.$id.']', 'judul', 'trim|required');
                    $this->form_validation->set_rules('text['.$id.']', 'deskripsi', 'trim|required');
                    $this->form_validation->set_rules('file_image['.$id.']', 'berkas gambar', 'trim|callback_file_selected|callback_file_check');
                    //if (empty($_FILES['file_image'][$id]['tmp_name'])) {
                        //$this->form_validation->set_rules('file_image['.$id.']', 'berkas gambar', 'required');
                    //}
                }
            }

         $this->form_validation->set_error_delimiters('', '');
		 $this->form_validation->run();

         $loop = $this->input->post('title');
         if(!empty($loop))
            {
                foreach($loop as $id => $value)
                {
                    if(form_error('title['.$id.']')!= '')
                    {
                        $data['inputerror'][] = 'title['.$id.']';
                        $data['error_id'][] = 'title_'.$id.'';
                        $string = form_error('title['.$id.']');
                        $result = str_replace(array('</p>', '<p>'), '', $string);
                        $data['error_string'][] = $result;
                        $data['status'] = FALSE;
                    }

                    if(form_error('text['.$id.']')!= '')
                    {
                        $data['inputerror'][] = 'text['.$id.']';
                        $data['error_id'][] = 'text_'.$id.'';
                        $string = form_error('text['.$id.']');
                        $result = str_replace(array('</p>', '<p>'), '', $string);
                        $data['error_string'][] = $result;
                        $data['status'] = FALSE;
                    }


                    $allowed =  array('png','jpg','jpeg','PNG','JPG','JPEG');
                    if (isset($_FILES['file_image'][$id])){
                        $new = $_FILES['file_image'][$id]['name'];
                        $ext = pathinfo($new, PATHINFO_EXTENSION);
                        if(!in_array($ext,$allowed) ) {
                            $data['inputerror'][] = 'file_image['.$id.']';
                            $data['error_id'][] = 'file_image_'.$id.'';
                            $data['file_id'][] = 'file_'.$id.'';
                            $string = 'Type File PNG JPG JPEG';
                            $result = str_replace(array('</p>', '<p>'),'',$string);
                            $data['error_string'][] = $result;
                            $data['error_file'][] = $result;
                            $data['status'] = FALSE;
                        }
                    }
                    if ((form_error('file_image['.$id.']') !== '')) {
                        $data['inputerror'][] = 'file_image['.$id.']';
                        $data['error_id'][] = 'file_image_'.$id.'';
                        $data['file_id'][] = 'file_'.$id.'';
                        $string = form_error('file_image['.$id.']');
                        $result = str_replace(array('</p>', '<p>'), '', $string);
                        $data['error_string'][] = $result;
                        $data['error_file'][] = $result;
                        $data['status'] = FALSE;
                    }
                }
            }

        if($data['status'] === FALSE){
            echo json_encode($data);
            exit();
		}
    }
    public function validate_edit() {
      $data = array();
      $data['error_string'] = array();
      $data['inputerror'] = array();
      $data['status'] = TRUE;

      $this->form_validation->set_rules('title_edit', 'judul', 'required|trim');
      $this->form_validation->set_rules('text_edit', 'deskripsi', 'required|trim');

      $this->form_validation->run();

      if ((form_error('title_edit') !== '')) {
          $data['inputerror'][] = 'title_edit';
          $string = form_error('title_edit');
          $result = str_replace(array('</p>', '<p>'), '', $string);
          $data['error_string'][] = $result;
          $data['status'] = FALSE;
      }
      if ((form_error('text_edit') !== '')) {
          $data['inputerror'][] = 'text_edit';
          $string = form_error('text_edit');
          $result = str_replace(array('</p>', '<p>'), '', $string);
          $data['error_string'][] = $result;
          $data['status'] = FALSE;
      }
      $allowed = array('gif', 'png', 'jpg', 'jpeg', 'GIF', 'PNG', 'JPG', 'JPEG');
        if (isset($_FILES['file_image'])) {
            $new = $_FILES['file_image']['name'];
            $ext = pathinfo($new, PATHINFO_EXTENSION);
            if (!in_array($ext, $allowed)) {
                $data['inputerror'][] = 'file_image';
                $string = 'Type File PNG JPG JPEG';
                $result = str_replace(array('</p>', '<p>'), '', $string);
                $data['error_string'][] = $result;
                $data['status'] = FALSE;
            }
        }
        if ((form_error('file_image') !== '')) {
            $data['inputerror'][] = 'file_image';
            $string = form_error('file_image');
            $result = str_replace(array('</p>', '<p>'), '', $string);
            $data['error_string'][] = $result;
            $data['status'] = FALSE;
        }

        if ($data['status'] === FALSE) {
            echo json_encode($data);
            exit();
        }
    }
    public function file_selected() {
        $this->form_validation->set_message('file_selected', 'foto diharuskan.');
        if (empty($_FILES['file_image']['name'])) {
            return FALSE;
        }else{
            return TRUE;
        }
    }
    public function file_check($str){
     $allowed_mime_type_arr = array('image/gif','image/jpeg','image/pjpeg','image/png','image/x-png');
     $mime = $_FILES['file_image']['type'];
        foreach($mime as $value) {
            if(isset($_FILES['file_image']['type']) && $_FILES['file_image']['type']!="") {
                if(in_array($value, $allowed_mime_type_arr)){
                    return TRUE;
                }else{
                    $this->form_validation->set_message('file_check', 'tipe file diharuskan gif/jpg/png file.');
                    return FALSE;
                }
            } else {
                $this->form_validation->set_message('file_check', 'foto diharuskan.');
                return FALSE;
            }
        }
    }
}

/* end of file Photo.php */
/* location: system/application/controllers/ */
