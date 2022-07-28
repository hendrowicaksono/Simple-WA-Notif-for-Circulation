<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 14/03/2021 18:03
 * @File name           : 1_CreateReadCounterTable.php
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

use SLiMS\Table\Schema;
use SLiMS\Table\Blueprint;

class CreateCircNotifWaLogTable extends \SLiMS\Migration\Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    function up()
    {
        \SLiMS\DB::getInstance()->query("
            CREATE TABLE circ_notif_wa_log (
                id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                member_id VARCHAR(35) NOT NULL,
                member_name VARCHAR(255) NOT NULL,
                member_type VARCHAR(255) NOT NULL,
                member_phone VARCHAR(255) NOT NULL,
                transaction_date datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                transaction_id VARCHAR(20) NOT NULL,
                message TEXT NOT NULL,
                created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX i_member_id (member_id),
                INDEX i_member_name (member_name),
                INDEX i_member_phone (member_phone),
                INDEX i_transaction_id (transaction_id),
                INDEX i_transaction_date (transaction_date)
            )
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    function down()
    {
        Schema::drop('circ_notif_wa_log');
        \SLiMS\DB::getInstance()->query("
            DROP TABLE circ_notif_wa_log
        ");
    }
}