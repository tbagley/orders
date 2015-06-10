<?php

namespace Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressHelper;

//error_reporting(0);

/**
 * @author
 *
 */
class ResetTestDataCommand extends Command
{

    public  $output = "";

    public  $yml_out = "";

    public  $alias_list = array();
    
    /**
     * @param
     */
    public function __construct()
    {
        parent::__construct();

        $core =& get_instance();
        $this->db_write = $core->db_write;
        $this->db_read = $core->db_read;
    }

    public function configure()
    {
        $this->setName('resettestdata');
        $this->setDescription('This will clear out all testing data');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        ob_start();

        $fp = fopen(LOGPATH.'cronlog.txt', 'a+');
        //$fp2 = fopen(LOGPATH.'crons/resettestdata.txt', 'a+');

        $this->output = $output;

        fwrite($fp, "\n### ".date('Y-m-d H:i:s')." Starting ResetTestDataCommand\n");
        //fwrite($fp2, "\n### ".date('Y-m-d H:i:s')." Starting ResetTestDataCommand\n");
        echo "\n### ".date('Y-m-d H:i:s')." Starting ResetTestDataCommand\n";
        //echo "\n### ".date('Y-m-d H:i:s')." Starting ResetTestDataCommand\n";
                        
        /* UNIT */                
        
        // get all the default unitgroups
        $default_unitgroup_ids = array();
        $default_unitgroups = $this->db_read->fetchAll("SELECT * FROM crossbones.unitgroup WHERE `default` = 1");
        if (! empty($default_unitgroups)) {            
            // assign all units to their default groups
            foreach ($default_unitgroups as $dug) {    
                $default_unitgroup_ids[] = $dug['unitgroup_id'];
                $this->db_write->update('crossbones.unit', array('unitgroup_id' => $dug['unitgroup_id']), array('account_id' => $dug['account_id']));
            }
        }
        $default_unitgroup_ids = implode(',', $default_unitgroup_ids);
        
        // delete all user unit group association EXCEPT the associations to default groups
        $this->db_write->executeQuery("DELETE FROM crossbones.user_unitgroup WHERE unitgroup_id NOT IN ({$default_unitgroup_ids})");

        // delete all non default unitgroups
        $this->db_write->delete('crossbones.unitgroup', array('`default`' => 0));
        
        // reset all landmark_id, boundary_id, and reference_id in unit events back to 0
        $units = $this->db_read->fetchAll("SELECT unit_id, db FROM crossbones.unit WHERE 1");
        if (! empty($units)) {
            $update = array('landmark_id', 'boundary_id', 'reference_id');
            $update = '`' . implode('` = 0,`', array_values($update)) . '` = 0';
            foreach ($units as $u) {
                $this->db_write->executeQuery("UPDATE {$u['db']}.unit{$u['unit_id']} SET {$update} WHERE 1");
            }
        }        
        
        
        /* TERRITORY */
        
        // truncate all territory
        $this->db_write->executeQuery("DELETE FROM crossbones.territory");
        
        // truncate all uploaded/incomplete territory
        $this->db_write->executeQuery("DELETE FROM crossbones.territoryupload");
        
        // delete all territory groups EXCEPT the default group
        $this->db_write->delete('crossbones.territorygroup', array('`default`' => 0));
        
        // truncate all unit territory association 
        $this->db_write->executeQuery("DELETE FROM crossbones.unit_territory");
        
        // get all the default territory group ids
        $default_territorygroup_ids = array();
        $default_territorygroups = $this->db_read->fetchAll("SELECT territorygroup_id FROM crossbones.territorygroup WHERE `default` = 1");
        if (! empty($default_territorygroups)) {
            foreach ($default_territorygroups as $dtg) {
                $default_territorygroup_ids[] = $dtg['territorygroup_id'];
            }
        }
        $default_territorygroup_ids = implode(',', $default_territorygroup_ids);
        
        // delete all user territory group association EXCEPT the associations to default groups
        $this->db_write->executeQuery("DELETE FROM crossbones.user_territorygroup WHERE territorygroup_id NOT IN ({$default_territorygroup_ids})");
        

        /* ALERT */
        
        // truncate all alerts
        $this->db_write->executeQuery("DELETE FROM crossbones.alert");
        
        // truncate all alert unit association
        $this->db_write->executeQuery("DELETE FROM crossbones.alert_unit");
        
        // truncate all alert territory association
        $this->db_write->executeQuery("DELETE FROM crossbones.alert_territory");
        
        // truncate all alert contact association
        $this->db_write->executeQuery("DELETE FROM crossbones.alert_contact");
        
        // truncate all alert history
        $this->db_write->executeQuery("DELETE FROM crossbones.alerthistory");
        
        // truncate all alert send
        $this->db_write->executeQuery("DELETE FROM crossbones.alertsend");
        
        // update all unit alert status to 0
        $params = array(
            'alertevent_id'         => 0, 
            'idleevent_id'          => 0, 
            'stopevent_id'          => 0, 
            'movingevent_id'        => 0, 
            'speedevent_id'         => 0, 
            'nonreportingstatus'    => 0, 
            'landmark_id'           => 0, 
            'reference_id'          => 0, 
            'boundary_id'           => 0
        );
                
        $update = '`' . implode('` = 0,`', array_keys($params)) . '` = 0';
        $this->db_write->executeQuery("UPDATE crossbones.unitalertstatus SET {$update} WHERE 1");        


        
        /* CONTACT */
        
        // delete standalone contacts (contacts not associated with users)
        $this->db_write->delete('crossbones.contact', array('user_id' => 0));
        
        // truncate contact group
        $this->db_write->executeQuery("DELETE FROM crossbones.contactgroup");
        
        // truncate contactgroup contact association
        $this->db_write->executeQuery("DELETE FROM crossbones.contactgroup_contact");
        
        
        /* REPORT */
        
        // truncate all scheduled reports
        $this->db_write->executeQuery("DELETE FROM crossbones.schedulereport");
        
        // truncate all scheduled report unit association
        $this->db_write->executeQuery("DELETE FROM crossbones.schedulereport_unit");
        
        // truncate all scheduled report territory association
        $this->db_write->executeQuery("DELETE FROM crossbones.schedulereport_territory");
        
        // truncate all scheduled report contact association
        $this->db_write->executeQuery("DELETE FROM crossbones.schedulereport_contact");

        // truncate all scheduled report user association
        $this->db_write->executeQuery("DELETE FROM crossbones.schedulereport_user");
                
        // truncate all report history
        $this->db_write->executeQuery("DELETE FROM crossbones.reporthistory");
        
        // truncate all report history unit association
        $this->db_write->executeQuery("DELETE FROM crossbones.reporthistory_unit");
        
        // truncate all report history territory association
        $this->db_write->executeQuery("DELETE FROM crossbones.reporthistory_territory");
        
        // truncate all report history user association
        $this->db_write->executeQuery("DELETE FROM crossbones.reporthistory_user");

        //fwrite($fp, "\n### ".date('Y-m-d H:i:s')."\n");
        //fwrite($fp2, "\n### ".date('Y-m-d H:i:s')."\n");
        echo "\n### ".date('Y-m-d H:i:s')."\n";
        //echo "\n### ".date('Y-m-d H:i:s')."\n";

        fwrite($fp, ob_get_contents());
        //fwrite($fp2, ob_get_contents());
        ob_end_clean();

        $output->writeln("<fg=red>Done</fg=red>");

        die();
    }
}