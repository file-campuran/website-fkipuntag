<?php

class Pendidikan extends MY_Controller{

    function __construct()
    {
        parent::__construct();

        $this->check_login();
        if ($this->session->userdata('id_role') != "1") {
            redirect('', 'refresh');
        }
        $this->load->model('sub_menu_model');
        $this->load->helper('url');
    }

    /*
     * Show pendidikan
     */
    function index()
    {
        $site = $this->Konfigurasi_model->listing();
        $data = array(
            'title'                 => 'pendidikan Baru | '.$site['nama_website'],
            'favicon'               => $site['favicon'],
            'site'                  => $site,
        );
        $this->template->load('alayout/template', 'admin/menu/pendidikan/index', $data);
    }

    function get_data()
	{
        $site = $this->Konfigurasi_model->listing();
		$list = $this->sub_menu_model->get_datatables(2);
        $data = array();
        $kata ="'Anda akan menghapus permanen data ini !'";
		$no = $_POST['start'];
		foreach ($list as $field) {
			$no++;
            $row = array();
            $row[]  = $no;
			$row[]  = $field->nama_sub_menu;
            $row[]  = '<a href="'.site_url('admin/menu/pendidikan/edit/'.$field->id_sub_menu).'" class="btn btn-warning btn-sm" title="Sunting Kata"><span class="fa  fa-pencil "></span></a>'.' <a href="'.site_url('admin/menu/pendidikan/hapus/'.$field->id_sub_menu).'" class="btn btn-danger btn-sm" title="Hapus Permanen"  onclick="return confirm('.$kata.')"><span class="fa fa-trash"></span></a>';
			$data[] = $row;
		}

		$output = array(
			"draw" => $_POST['draw'],
			"recordsTotal" => $this->sub_menu_model->count_all(),
			"recordsFiltered" => $this->sub_menu_model->count_filtered(),
			"data" => $data,
        );
        
		//output dalam format JSON
		echo json_encode($output);
	}

    /*
     * Adding a new pendidikan
     */
    function tambah()
    {   
        $site = $this->Konfigurasi_model->listing();
        $data = array(
            'title'                 => 'pendidikan | '.$site['nama_website'],
            'favicon'               => $site['favicon'],
            'site'                  => $site,
        );
        $this->load->library('form_validation');
        $this->form_validation->set_rules('nama_sub_menu','Sub Menu','max_length[200]|required|is_unique[sub_menu.nama_sub_menu]');
        if($this->form_validation->run())
        {  
            $params = array(
                'teks'          => $this->input->post('teks'),
                'nama_sub_menu' => $this->input->post('nama_sub_menu'),
                'slug_sub_menu' => slug($this->input->post('nama_sub_menu',TRUE)),
                'id_menu'       => '2'
            );
            if (!empty($_FILES['gambar']['name'])) {
                $nama_gambar    = $this->upload_foto($this->input->post('nama_sub_menu'));
                $params['gambar'] = $nama_gambar;
            }

            $this->sub_menu_model->input_data($params);
            redirect('admin/menu/pendidikan');
        }
        else
        {
            $this->template->load('alayout/template', 'admin/menu/pendidikan/add', $data);
            
        }
    }

    function edit($id_sub_menu)
    {   $site = $this->Konfigurasi_model->listing();
        $data = array(
            'title'                 => 'Edit pendidikan | '.$site['nama_website'],
            'favicon'               => $site['favicon'],
            'site'                  => $site,
        );
        $data['pendidikan'] = $this->sub_menu_model->detail_data($id_sub_menu);
        $this->template->load('alayout/template', 'admin/menu/pendidikan/edit', $data);
    }

    function update($id_sub_menu)
    {   
        $data = array(
            'nama_sub_menu' => $this->input->post('nama_sub_menu'),
            'teks'          => $this->input->post('teks'),
            'slug_sub_menu' => slug($this->input->post('nama_sub_menu',TRUE)),
            'id_menu'       => '2'
        );
        if (!empty($_FILES['gambar']['name'])) {
            $nama_gambar    = $this->upload_foto($this->input->post('nama_sub_menu'));
            $data['gambar'] = $nama_gambar;
        }
        $this->sub_menu_model->update_data($data, $id_sub_menu);
        redirect('admin/menu/pendidikan/edit/'.$id_sub_menu);

    }


    function hapus($id)
    {   
        $pendidikan = $this->sub_menu_model->detail_data($id);
        if(isset($pendidikan->id_sub_menu))
        {
            $this->sub_menu_model->hapus_data($pendidikan->id_sub_menu);
            unlink('./assets/upload/foto_artikel/'.$pendidikan->gambar);
            redirect('admin/menu/pendidikan');
        }
        else
            show_error('Data pendidikan tidak ada');
    }

    function upload_foto($nama){
        $set_name   = $nama."-".RAND(00000,99999);
        $path       = $_FILES['gambar']['name'];
        $extension  = ".".pathinfo($path, PATHINFO_EXTENSION);

        $config['upload_path']          = './assets/upload/foto_artikel/';
        $config['allowed_types']        = 'gif|jpg|jpeg|png';
        $config['max_size']             = 9024;
        $config['width']                = 500;
        $config['height']               = 500;
        $config['file_name']            = "$set_name".$extension;
        $this->load->library('upload', $config);
        // proses upload
        $upload = $this->upload->do_upload('gambar');

        if ( ! $upload )
        {
            $error = array('error' => $this->upload->display_errors());
            $this->template->load('alayout/template', 'admin/menu/pendidikan/add', $error);
        }

        $upload = $this->upload->data();
        return $upload['file_name'];
    }

    function hapusgambar($id_sub_menu)
    {
        $pendidikan = $this->sub_menu_model->detail_data($id_sub_menu);

        if(isset($pendidikan->id_sub_menu))
        {
            unlink('./assets/upload/foto_artikel/'.$pendidikan->gambar);
            $data['gambar'] = "";
            $this->sub_menu_model->update_data($data, $pendidikan->id_sub_menu);
            redirect('admin/menu/pendidikan/edit/'.$id_sub_menu);
        }
        else
          show_error('Data Gambar tidak ada');
    }

}
