require_once __DIR__ . '/../../middlewares/authorize.php';

$router->post('/api/admin/staff', function () {
    authorize(['ADMIN']);
    (new StaffController())->create();
});
