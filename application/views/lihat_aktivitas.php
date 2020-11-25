<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="utf-8">
  <title>Lihat Data</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css">
  <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
</head>
<body>
  <div class="offset-md-1 col-md-10 mt-5">
    <a href="<?=base_url("admin/aktivitas")?>" class="btn btn-primary">Tambah Data</a>
    <br/><br/>
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
    <div class="row">
      <div class="form-group col-md-6">
        Aktivitas :
        <select class="form-control" name="aktivitas" id="aktivitas">
          <option value=""></option>
          <?php foreach ($aktivitas as $a): ?>
            <option value="<?=$a->id?>"><?=$a->nama?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group col-md-12">
        <button type="button" name="cari" class="btn btn-secondary" id="cari">Cari</button>
      </div>
  </form>
    <!-- <table class="table" id="tblAktivitas">
      <thead class="thead-dark">
        <tr>
          <th scope="col">#</th>
          <th scope="col">First</th>
          <th scope="col">Last</th>
          <th scope="col">Handle</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <th></th>
          <td></td>
        </tr>
      </tbody>
    </table> -->
    <br><br><br>
  </div>
  <?php echo unserialize($list); ?>
  <script type="text/javascript">
  $("#cari").click(function() {
    let id_aktivitas = $('#aktivitas').val();
    console.log(id_aktivitas);
    window.location.href = '<?=base_url("admin/aktivitas/lihat")?>/'+id_aktivitas;
  });

  $('#tblAktivitas').DataTable({
    "pageLength": 25
  });
  </script>
</body>
</html>
