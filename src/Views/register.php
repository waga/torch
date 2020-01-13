<?php echo view('App\Libraries\Torch\Views\layout\header'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Registration</div>
                <div class="card-body">
                    <?php if (isset($errors)) { ?>
                        <div class="errors">
                            <?php foreach ($errors as $errorField => $errorMessage) { ?>
                                <div class="alert alert-danger" role="alert"><?php echo $errorMessage; ?></div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                    <form method="POST" action="<?php echo route_to('admin-register'); ?>">
                        <!--
                        <div class="form-group row">
                            <label for="username" class="col-md-4 col-form-label text-md-right">Username</label>
                            <div class="col-md-6">
                                <input id="username" type="username" class="form-control " name="username" value="" required="" autocomplete="username" autofocus="" />
                            </div>
                        </div>
                        -->
                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">E-Mail Address</label>
                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control " name="email" value="" required="" autocomplete="email" autofocus="" />
                            </div>
                        </div>
                        <!--
                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">Name</label>
                            <div class="col-md-6">
                                <input id="name" type="name" class="form-control " name="name" value="" required="" autocomplete="name" autofocus="" />
                            </div>
                        </div>
                        -->
                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">Password</label>
                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control " name="password" required="" autocomplete="current-password" />
                            </div>
                        </div>
                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    Register
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo view('App\Libraries\Torch\Views\layout\footer'); ?>
