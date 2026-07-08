<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use App\Models\Segment;
use App\Models\LinesOrGroup;
use App\Models\Machine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Roles
        $roles = ['operator', 'line_leader', 'mechanic', 'floor_incharge', 'maintenance_head', 'maintenance_manager'];
        foreach ($roles as $role) {
            Role::create(['name' => $role]);
        }

        // 2. Seed Target Users (Password for all: password)
        $password = Hash::make('password');

        $op = User::create(['name' => 'John Operator', 'email' => 'operator@factory.com', 'password' => $password, 'role_id' => 1]);
        $ll = User::create(['name' => 'Alice Leader', 'email' => 'leader@factory.com', 'password' => $password, 'role_id' => 2]);
        $mechParts = User::create(['name' => 'Mike Parts-Mech', 'email' => 'mech1@factory.com', 'password' => $password, 'role_id' => 3]);
        $mechAssy = User::create(['name' => 'Bob Assy-Mech', 'email' => 'mech2@factory.com', 'password' => $password, 'role_id' => 3]);
        $incharge = User::create(['name' => 'Frank Incharge', 'email' => 'incharge@factory.com', 'password' => $password, 'role_id' => 4]);

        // 3. Seed Segments
        $partsSegment = Segment::create(['name' => 'parts']);
        $assemblySegment = Segment::create(['name' => 'assembly']);

        // 4. Map Mechanics to Segments
        DB::table('segment_mechanics')->insert([
            ['user_id' => $mechParts->id, 'segment_id' => $partsSegment->id],
            ['user_id' => $mechAssy->id, 'segment_id' => $assemblySegment->id],
        ]);

        // 5. Seed Groups/Lines & Their 15 Machines Each
        $partsGroups = ['F1', 'B1', 'CUFF1', 'SLV1', 'CLR1'];
        foreach ($partsGroups as $groupName) {
            $group = LinesOrGroup::create(['segment_id' => $partsSegment->id, 'name' => $groupName]);
            for ($i = 1; $i <= 15; $i++) {
                Machine::create([
                    'line_or_group_id' => $group->id,
                    'machine_code' => "MAC-{$groupName}-" . str_pad($i, 2, '0', STR_PAD_LEFT)
                ]);
            }
        }

        $assemblyLines = ['Line 1', 'Line 2', 'Line 3', 'Line 4'];
        foreach ($assemblyLines as $lineName) {
            $group = LinesOrGroup::create(['segment_id' => $assemblySegment->id, 'name' => $lineName]);
            for ($i = 1; $i <= 15; $i++) {
                Machine::create([
                    'line_or_group_id' => $group->id,
                    'machine_code' => "MAC-L" . substr($lineName, -1) . "-" . str_pad($i, 2, '0', STR_PAD_LEFT)
                ]);
            }
        }
    }
}
