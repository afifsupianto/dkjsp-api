<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="utf-8">
  <title>Tambah Data</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
  <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
</head>
<body>
  <div class="offset-md-1 col-md-10 mt-5">
    <?php if($this->session->flashdata('sukses_topik')){ ?>
    <div class="alert alert-success" role="alert">
      <?php echo $this->session->flashdata('sukses_topik'); ?>
    </div>
    <?php } ?>
    <?php if($this->session->flashdata('sukses_soal')){ ?>
      <div class="alert alert-success" role="alert">
        <?php echo $this->session->flashdata('sukses_soal'); ?>
      </div>
    <?php } ?>
    <h1>Tambah Data Soal & Jawaban</h1>
    <form action="<?=base_url("admin/aktivitas")?>" method="post">
      <div class="row">
        <div class="form-group col-md-6">
          <label for="exampleInputEmail1">Aktivitas</label>
          <select class="form-control" name="aktivitas">
            <?php foreach ($aktivitas as $a): ?>
              <option value="<?=$a->id?>"><?=$a->nama?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group col-md-6">
          <label for="exampleInputEmail1">Tipe</label>
          <select class="form-control" name="tipe">
            <option value="0">Check Box</option>
            <option value="1">Radio Button</option>
            <option value="2">Uraian</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label for="exampleInputEmail1">Topik</label>
        <select class="form-control" name="topik">
          <?php foreach ($topik as $a): ?>
            <option value="<?=$a->id?>"><?=$a->nama?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label for="exampleInputPassword1">Soal</label>
        <input type="text" class="form-control" name="soal" placeholder="Soal">
      </div>
      <div class="" id="jawabans">
        <div class="form-group">
          <label for="exampleInputPassword1">Jawaban</label>
          <input type="text" class="form-control" name="jawaban[]" placeholder="Jawaban">
        </div>
      </div>
      <div class="form-group">
        <button type="button" id="tambahJawaban" class="btn btn-secondary">Tambah Jawaban</button>
      </div>
      <button name="simpan" type="submit" class="btn btn-success col-md-12">Submit</button>
    </form>
    <br><br><br>
    <hr>
    <h1>Tambah Data Topik</h1>
    <form action="<?=base_url("admin/aktivitas/topik")?>" method="post">
      <div class="form-group">
        <label for="exampleInputPassword1">Topik</label>
        <input type="text" class="form-control" name="topik" placeholder="Topik">
      </div>
      <button name="simpan_topik" type="submit" class="btn btn-success col-md-12">Submit</button>
    </form>
    <br><br><br>
  </div>
  <script type="text/javascript">
  $( "#tambahJawaban" ).click(function() {
    $('#jawabans').append(`<div class="form-group">
    <label for="exampleInputPassword1">Jawaban</label>
    <input type="text" class="form-control" name="jawaban[]" placeholder="Jawaban">
    </div> `)
  });
  </script>
</body>
</html>
