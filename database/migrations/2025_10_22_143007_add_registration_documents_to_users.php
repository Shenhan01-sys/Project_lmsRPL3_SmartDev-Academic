<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table("users", function (Blueprint $table) {
            // Registration documents fields
            $table->string("ktp_orang_tua_path")->nullable();
            $table->string("ijazah_path")->nullable();
            $table->string("foto_siswa_path")->nullable();
            $table->string("bukti_pembayaran_path")->nullable();

            // Registration status and metadata
            $table
                ->enum("registration_status", [
                    "pending_documents",
                    "pending_payment",
                    "pending_approval",
                    "approved",
                    "rejected",
                ])
                ->default("pending_documents");

            // Approval metadata
            $table->unsignedBigInteger("approved_by")->nullable();

            // Foreign key constraint
            $table->foreign("approved_by")->references("id")->on("users");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("users", function (Blueprint $table) {
            $table->dropForeign(["approved_by"]);
            $table->dropColumn([
                "ktp_orang_tua_path",
                "ijazah_path",
                "foto_siswa_path",
                "bukti_pembayaran_path",
                "registration_status",
                "approved_by",
            ]);
        });
    }
};
