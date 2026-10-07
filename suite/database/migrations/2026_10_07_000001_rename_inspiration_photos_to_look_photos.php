<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * appointment_inspiration_photos has held two kinds of photo since saved
 * looks: the client's inspiration and the staff "after" photos. The name
 * made `inspiration` code silently miss the after photos, so the table (and
 * model, AppointmentLookPhoto) is now named for what it holds.
 *
 * Only the table is renamed. Column names, the `inspiration/` storage folder
 * (real files live there) and the existing index/constraint names stay as
 * they are. Old audit-log rows are pointed at the new class name so the
 * audit trail reads as one history.
 */
return new class extends Migration {
    private const OLD_CLASS = 'App\\Modules\\Booking\\Models\\AppointmentInspirationPhoto';
    private const NEW_CLASS = 'App\\Modules\\Booking\\Models\\AppointmentLookPhoto';

    public function up(): void
    {
        Schema::rename('appointment_inspiration_photos', 'appointment_look_photos');

        if (Schema::hasTable('audit_logs')) {
            DB::table('audit_logs')->where('entity_type', self::OLD_CLASS)->update(['entity_type' => self::NEW_CLASS]);
        }
    }

    public function down(): void
    {
        Schema::rename('appointment_look_photos', 'appointment_inspiration_photos');

        if (Schema::hasTable('audit_logs')) {
            DB::table('audit_logs')->where('entity_type', self::NEW_CLASS)->update(['entity_type' => self::OLD_CLASS]);
        }
    }
};
