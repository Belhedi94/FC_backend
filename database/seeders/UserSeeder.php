<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Carbon\Carbon;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'full_name' => 'Rafaa Belhedi',
            'email' => 'rafaa.b@gmail.com',
            'password' => bcrypt('Rafaa94$'),
            'mobile_number' => '21641056519',
            'is_admin' => true,
            'is_active' => true,
            'role_id' => 1,
        ]);
    }
}
