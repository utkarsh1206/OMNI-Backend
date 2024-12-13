<style>
	.link {
		font-size: 16px;
		color: #000000;
		font-family: Arial;
	}

	.link:hover {
		font-size: 16px;
		color: #808080;
		font-family: Arial;
	}

	.title {
		font-size: 18px;
		color: #424242;
		font-family: Arial;
		font-weight: bold;
		text-decoration: underline;
	}

	.sub_title {
		font-size: 14px;
		color: #424242;
		font-family: Arial;
		font-weight: bold;
		text-decoration: none;
		padding-left: 20px;
	}

	.label {
		font-size: 12px;
		color: #000000;
		font-family: Arial;
	}

	.outer_table_border {
		border: 1px solid #000000;
	}

	.inner_table_border {
		border: 1px solid #CDCDCD;
	}

	.mendatory_field {
		font-size: 18px;
		color: #FF0000;
		font-family: Arial;
	}
</style>

<?php
#################################################################
# Dial Center License Generator                                 #
#################################################################

//TODO EC following is AES algorithm
include 'AES.php';

$req_action = "ENCODE_OR_DECODE";

if (($_REQUEST['operation_mode'] != "") && ($_REQUEST['operation_mode'] != "ENCODE_OR_DECODE")) {
	$req_action = $_REQUEST['operation_mode'];
}
//TODO EC Neox Dial Center IP Address
// $server_ip = '10.106.1.44';

//TODO EC function for AES encryption decryption log
// function AES_log($message)
// {
// 	$LogFileName = "DC_License_Generation";

// 	if (file_exists($LogFileName))
// 	{
// 		$fh = fopen($LogFileName, 'a');
// 		fwrite($fh, $message."\n");
// 	}
// 	else
// 	{
// 		$fh = fopen($LogFileName, 'w');
// 		fwrite($fh, $message."\n");
// 	}
// 	fclose($fh);
// }

?>
<html>

<head>
	<title>Omni License Generator</title>

	<link rel="stylesheet" href="popcalendar.css">
	<script language="JavaScript" src="popcalendar.js"></script>

	<script>
		setContextPathForImageDir('..');

		function popUpCalendar(ctl, ctl2, datestyle) {
			datestyle = 'yyyy-MM-dd';
			jsPopUpCalendar(ctl, ctl2, datestyle);

		}
	</script>


	<script type="text/javascript">
		function encode_decode_license(modes) {
			if (modes == "ENCODE") {
				document.getElementById('visibility_date_row').style.display = '';
				document.getElementById('number_of_enterprise_row').style.display = '';
				document.getElementById('number_of_agents_row').style.display = '';
				document.getElementById('number_of_live_agents_row').style.display = '';
				//NEOX-2989, Acl users support for license
				document.getElementById('number_of_acl_users_row').style.display = '';
				document.getElementById('customer_name_row').style.display = '';
				document.getElementById('hostid_row').style.display = '';
				document.getElementById('license_key_row').style.display = 'none';
			} else {
				document.getElementById('visibility_date_row').style.display = 'none';
				document.getElementById('number_of_enterprise_row').style.display = 'none';
				document.getElementById('number_of_agents_row').style.display = 'none';
				document.getElementById('number_of_live_agents_row').style.display = 'none';
				//NEOX-2989, Acl users support for license
				document.getElementById('number_of_acl_users_row').style.display = 'none';
				document.getElementById('customer_name_row').style.display = '';
				document.getElementById('hostid_row').style.display = '';
				document.getElementById('license_key_row').style.display = '';
			}
		}

		function validate_license_data() {
			if (document.getElementById('encode_mode').checked == true) {
				var mode = "ENCODE";
			} else {
				var mode = "DECODE";
			}
			if (mode == "ENCODE") {
				if (document.form_license.customer_name.value == "") {
					alert('\nPlease enter customer name\n');
					document.form_license.customer_name.focus();
					return false;
				}
				if (document.form_license.validity_date.value == "") {
					alert('\nPlease enter validity date\n');
					document.form_license.validity_date.focus();
					return false;
				}
				if(document.form_license.number_of_enterprise.value == "")
				{
					alert('\nPlease enter number of enterprise\n');
					document.form_license.number_of_enterprise.focus();
					return false;
				}
				if (document.form_license.number_of_agents.value == "") {
					alert('\nPlease enter number of agents\n');
					document.form_license.number_of_agents.focus();
					return false;
				}
				//GUNJAN
				if (document.form_license.number_of_agents.value == 0) {
					alert('\nPlease enter value more than 0\n');
					document.form_license.number_of_agents.focus();
					return false;
				}
				if (document.form_license.number_of_live_agents.value == "") {
					alert('\nPlease enter number of live agents\n');
					document.form_license.number_of_live_agents.focus();
					return false;
				}

				if (document.form_license.number_of_live_agents.value == 0) {
					alert('\nPlease enter value more than 0\n');
					document.form_license.number_of_live_agents.focus();
					return false;
				}

				//NEOX-2989,Validation for number of acl users
				if (document.form_license.number_of_acl_users.value == "") {
					alert('\nPlease enter number of acl users\n');
					document.form_license.number_of_acl_users.focus();
					return false;
				}
				//NEXO-2989, END

				if (document.form_license.number_of_acl_users.value == 0) {
					alert('\nPlease enter value more than 0\n');
					document.form_license.number_of_acl_users.focus();
					return false;
				}
				if (document.form_license.hostid.value == "") {
					alert('\nPlease enter hostid of server\n');
					document.form_license.hostid.focus();
					return false;
				}
			} else {
				if (document.form_license.customer_name.value == "") {
					alert('\nPlease enter customer name\n');
					document.form_license.customer_name.focus();
					return false;
				}
				if (document.form_license.hostid.value == "") {
					alert('\nPlease enter hostid of server\n');
					document.form_license.hostid.focus();
					return false;
				}
				if (document.form_license.license_key.value == "") {
					alert('\nPlease enter license key\n');
					document.form_license.license_key.focus();
					return false;
				}
			}
		}
	</script>
</head>

<body>
	<br />
	<br />
	<table width="55%" cellspacing="3" class="outer_table_border" align="center">
		<tr height="10">
			<td></td>
		</tr>
		<?php
		if ($req_action == "ENCODE_OR_DECODE") {
		?>
			<tr>
				<td colspan="7" align=center>
					<form name="form_license" method="GET">
						<input type="hidden" name="operation_mode" value="DISPLAY">
						<table width="90%" cellspacing="10" class="inner_table_border" border="0">
							<tr>
								<td colspan="5" align="center" class="title">Omni License Generator</td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td align="left" width="25%" class="label">License Encode/Decode</td>
								<td align="left" class="label">:</td>
								<td align="left" class="label"><input type="radio" name="mode" id="encode_mode" value="ENCODE" onclick="encode_decode_license('ENCODE')" checked>ENCODE <input type="radio" name="mode" value="DECODE" id="decode_mode" onclick="encode_decode_license('DECODE')">DECODE
								</td>
								<td>&nbsp;</td>
							</tr>
							<tr id="customer_name_row" style="display:bolck;">
								<td>&nbsp;</td>
								<td align="left" class="label">Customer Name <span class="mendatory_field">*</span></td>
								<td align="left" class="label">:</td>
								<td align="left" class="label"><input type="text" id="customer_name" class="label" name="customer_name" value="">&nbsp;</td>
								<td>&nbsp;</td>
							</tr>
							<tr id="visibility_date_row" style="display_block;">
								<td>&nbsp;</td>
								<td align="left" class="label">Validity Date <span class="mendatory_field">*</span></td>
								<td align="left" class="label">:</td>
								<td align="left" class="label">
									<!--<input type="text" id="validity_date"  class="label" name="validity_date" value=""> -->
									<INPUT class="label" type="text" name="validity_date" id="validity_date" value=""><a onclick="popUpCalendar(this, document.form_license.validity_date)" href="javascript:void(0)"><img src="calendar.jpg" border=0 alt=calendar /></a>
									&nbsp; (Expected Date Format: YYYY-MM-DD)
								</td>
								<td>&nbsp;</td>
							</tr>
							<tr id="number_of_enterprise_row" style="display:block;">
							<td>&nbsp;</td>
							<td align="left" class="label">Number of Enterprise <span class="mendatory_field">*</span></td>
							<td align="left" class="label">:</td>
							<td align="left" class="label"><input type="text" class="label" name="number_of_enterprise" id="number_of_enterprise" value="" size="6" maxlength="5">
							&nbsp; (Digits only)</td>
							<td>&nbsp;</td>
						</tr>
							<tr id="number_of_agents_row" style="display:block;">
								<td>&nbsp;</td>
								<td align="left" class="label">Number of Agents <span class="mendatory_field">*</span></td>
								<td align="left" class="label">:</td>
								<td align="left" class="label"><input type="text" class="label" name="number_of_agents" id="number_of_agents" value="" size="6" maxlength="5">
									&nbsp; (Digits only)</td>
								<td>&nbsp;</td>
							</tr>
							<tr id="number_of_live_agents_row" style="display:block;">
								<td>&nbsp;</td>
								<td align="left" class="label">Number of Live Agents <span class="mendatory_field">*</span></td>
								<td align="left" class="label">:</td>
								<td align="left" class="label"><input class="label" type="text" name="number_of_live_agents" value="" size="6" maxlength="5">
									&nbsp; (Digits only)</td>
								<td>&nbsp;</td>
							</tr>

							<!--NEOX-2989, Number of Acl Users field, START -->
							<tr id="number_of_acl_users_row" style="">
								<td>&nbsp;</td>
								<td align="left" class="label">Number of ACL Users <span class="mendatory_field">*</span></td>
								<td align="left" class="label">:</td>
								<td align="left" class="label"><input class="label" type="text" name="number_of_acl_users" value="" size="6" maxlength="5">
									&nbsp; (Digits only)</td>
								<td>&nbsp;</td>
							</tr>
							<!--NEOX-2989, END -->

							<tr id="hostid_row" style="">
								<td>&nbsp;</td>
								<td align="left" class="label">Hostid <span class="mendatory_field">*</span></td>
								<td align="left" class="label">:</td>
								<td align="left" class="label"><input type="text" id="hostid" class="label" name="hostid" value="">
									&nbsp; (Valid hostid of server Exa: 6a0ad501)</td>
								<td>&nbsp;</td>
							</tr>
							<tr id="license_key_row" style="display:none;">
								<td>&nbsp;</td>
								<td align="left" class="label">License Key <span class="mendatory_field">*</span></td>
								<td align="left" class="label">:</td>
								<td align="left"><textarea name="license_key" id="license_key" rows="6" cols="55"></textarea></td>
								<td>&nbsp;</td>
							</tr>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td align="left">&nbsp;</td>
				<td align="left" colspan="2"><input class="label" type="submit" name="submit" value="GENERATE LICENSE" onclick="return validate_license_data();"></td>
				<td>&nbsp;</td>
			</tr>

	</table>
	</form>

	<script>
		encode_decode_license('ENCODE');
	</script>

	</td>
	</tr>
	<?php
		}
		if ($req_action == "DISPLAY") {
			$validity_date 		= $_REQUEST['validity_date'];
			$number_of_enterprise 	= $_REQUEST['number_of_enterprise'];
			$number_of_agents 	= $_REQUEST['number_of_agents'];
			$number_of_live_agents 	= $_REQUEST['number_of_live_agents'];
			//NEOX-2989, Number of Acl users request
			$number_of_acl_users = $_REQUEST['number_of_acl_users'];
			$mode 			= $_REQUEST['mode'];
			$customer_name		= $_REQUEST['customer_name'];
			$license_key		= $_REQUEST['license_key'];
			$inputKey		= $_REQUEST['hostid'];

			//TODO EC following is to encode license key based on provided data
			if ($mode == "ENCODE") {
				//Following varieble holds the dial center final license key
				$dc_license = '';

				//Encrypted text generation for validity date
				$aes = new AES($validity_date, $inputKey, $blockSize);
				$enc = $aes->encrypt();
				$aes->setData($enc);

				$dc_license .= $enc . $seprator;

				//Encrypted text generation for number of Enterprise
				$aes = new AES($number_of_enterprise, $inputKey, $blockSize);
				$enc = $aes->encrypt();
				$aes->setData($enc);

				$dc_license .= $enc . $seprator;

				//Encrypted text generation for number of agents
				$aes = new AES($number_of_agents, $inputKey, $blockSize);
				$enc = $aes->encrypt();
				$aes->setData($enc);

				$dc_license .= $enc . $seprator;

				//Encrypted text generation for number of live agents
				$aes = new AES($number_of_live_agents, $inputKey, $blockSize);
				$enc = $aes->encrypt();
				$aes->setData($enc);

				#NEOX-2989, TODO Added new ACL users support.
				$dc_license .= $enc . $seprator;

				#NEOX-2989, Encrypted text generation for number of acl users, START
				$aes = new AES($number_of_acl_users, $inputKey, $blockSize);
				$enc = $aes->encrypt();
				$aes->setData($enc);
				#NEOX-2989, END

				$dc_license .= $enc;

				// $NOW_DATE = date("Y-m-d h:m:s");
				// $LogStr = "------------------------------------------------Dial Center License Generation----------------------------------------------------------\n";
				// #NEOX-2989, Added Number of ACL users
				// $LogStr .= "DateTime:$NOW_DATE\nMode: $mode\nCustomer Name: $customer_name\nValidity Date: $validity_date\nNumber of Agents: $number_of_agents\nNumber of Live Agents: $number_of_live_agents\nNumber of ACL Users: $number_of_acl_users\nHostid: $inputKey\nGenerated License: $dc_license\n";
				// $LogStr .= "----------------------------------------------------------------------------------------------------------------------------------------\n\n";
				// AES_log($LogStr);

	?>
		<tr>
			<td colspan="8" align=center>
				<table width="90%" cellspacing="11" style="border:1px solid #CDCDCD;" border="0">
					<tr>
						<td colspan="5" align="center" class="title"><a class="title" href="index.php?operation_mode=ENCODE_OR_DECODE">Go Back To License Generator Screen</a></td>
					</tr>

					<tr>
						<td colspan="5" align="left" class="sub_title">Provided Data for License Generation</td>
					</tr>

					<tr>
						<td>&nbsp;</td>
						<td align="left" width="35%" class="label">License Encode/Decode</td>
						<td align="left" class="label">:</td>
						<td align="left" class="label"><?php echo $mode; ?>
						</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td align="left" class="label">Customer Name </td>
						<td align="left" class="label">:</td>
						<td align="left" class="label"><?php echo $customer_name; ?>
							&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td align="left" class="label">Validity Date</td>
						<td align="left" class="label">:</td>
						<td align="left" class="label"><?php echo $validity_date; ?></td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td align="left" class="label">Number of Enterprise</td>
						<td align="left" class="label">:</td>
						<td align="left" class="label"><?php echo $number_of_enterprise; ?></td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td align="left" class="label">Number of Agents</td>
						<td align="left" class="label">:</td>
						<td align="left" class="label"><?php echo $number_of_agents; ?></td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td align="left" class="label">Number of Live Agents</td>
						<td align="left" class="label">:</td>
						<td align="left" class="label"><?php echo $number_of_live_agents; ?></td>
						<td>&nbsp;</td>
					</tr>
					<!--NEOX-2989, Number of ACL users, START -->
					<tr>
						<td>&nbsp;</td>
						<td align="left" class="label">Number of ACL Users</td>
						<td align="left" class="label">:</td>
						<td align="left" class="label"><?php echo $number_of_acl_users; ?></td>
						<td>&nbsp;</td>
					</tr>
					<!--NEOX-2989, END -->
					<tr>
						<td>&nbsp;</td>
						<td align="left" class="label">Hostid</td>
						<td align="left" class="label">:</td>
						<td align="left" class="label"><?php echo $inputKey; ?></td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td align="left" class="label" valign="top">License Key</td>
						<td align="left" class="label" valign="top">:</td>
						<td align="left" class="label"><textarea name="license_key" id="license_key" rows="6" cols="55" readonly>
						<?php echo trim($dc_license); ?></textarea></td>
						<td>&nbsp;</td>
					</tr>
				</table>
			<?php
			} else {
				//Parameter parsing from license
				$param = explode("$seprator", $license_key);
				$Evalidity_date = $param[0];
				$Enumber_of_enterprise = $param[1];
				$Enumber_of_agents = $param[2];
				$Enumber_of_live_agents = $param[3];
				#NEOX-2989, TODO acl users support
				$Enumber_of_acl_users = $param[4];

				//Decypted text generation for validity date
				$aes = new AES($Evalidity_date, $inputKey, $blockSize);
				$aes->setData($Evalidity_date);
				$Dvalidity_date = $aes->decrypt();

				//Decrypted text generation for number of agents
				$aes = new AES($Enumber_of_enterprise, $inputKey, $blockSize);
				$aes->setData($Enumber_of_enterprise);
				$Dnumber_of_enterprise = $aes->decrypt();

				//Decrypted text generation for number of agents
				$aes = new AES($Enumber_of_agents, $inputKey, $blockSize);
				$aes->setData($Enumber_of_agents);
				$Dnumber_of_agents = $aes->decrypt();

				//Decrypted text generation number of live agents
				$aes = new AES($Enumber_of_live_agents, $inputKey, $blockSize);
				$aes->setData($Enumber_of_live_agents);
				$Dnumber_of_live_agents = $aes->decrypt();

				#NEOX-2989, TODO Decrypted text generation number of acl users
				$aes = new AES($Enumber_of_acl_users, $inputKey, $blockSize);
				$aes->setData($Enumber_of_acl_users);
				$Dnumber_of_acl_users = $aes->decrypt();

				$NOW_DATE = date("Y-m-d h:m:s");
				// $LogStr = "------------------------------------------------Dial Center License Generation----------------------------------------------------------\n";
				// #NEOX-2989, Added Acl users
				// $LogStr .= "DateTime: $NOW_DATE\nMode: $mode\nProvided License Key: $license_key\nCustomer Name: $customer_name\nValidity Date: $Dvalidity_date\nNumber of Agents: $Dnumber_of_agents\nNumber of Live Agents: $Dnumber_of_live_agents\nNumber of ACL Users: $Dnumber_of_acl_users\nHostid: $inputKey\n";
				// $LogStr .= "----------------------------------------------------------------------------------------------------------------------------------------\n\n";
				// AES_log($LogStr);

			?>
		<tr>
			<td colspan="7" align=center>
				<table width="90%" cellspacing="10" style="border:1px solid #CDCDCD;" border="0">
					<tr>
						<td colspan="5" align="center" class="title"><a class="title" href="index.php?operation_mode=ENCODE_OR_DECODE">Go Back To License Generator Screen</a></td>
					</tr>
					<tr>
						<td colspan="5" align="left" class="sub_title">Decoded Data From License Key</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td align="left" width="35%" class="label">License Encode/Decode</td>
						<td align="left" class="label">:</td>
						<td align="left" class="label"><?php echo $mode; ?>
						</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td align="left" class="label">Customer Name </td>
						<td align="left" class="label">:</td>
						<td align="left" class="label"><?php echo $customer_name; ?>
							&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td align="left" class="label">Validity Date</td>
						<td align="left" class="label">:</td>
						<td align="left" class="label"><?php echo $Dvalidity_date; ?></td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td align="left" class="label">Number of Enterprise</td>
						<td align="left" class="label">:</td>
						<td align="left" class="label"><?php echo $Dnumber_of_enterprise; ?></td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td align="left" class="label">Number of Agents</td>
						<td align="left" class="label">:</td>
						<td align="left" class="label"><?php echo $Dnumber_of_agents; ?></td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td align="left" class="label">Number of Live Agents</td>
						<td align="left" class="label">:</td>
						<td align="left" class="label"><?php echo $Dnumber_of_live_agents; ?></td>
						<td>&nbsp;</td>
					</tr>


					<!--NEOX-2989, Display decrypted Number of acl users, START -->
					<tr>
						<td>&nbsp;</td>
						<td align="left" class="label">Number of ACL Users</td>
						<td align="left" class="label">:</td>
						<td align="left" class="label"><?php echo $Dnumber_of_acl_users; ?></td>
						<td>&nbsp;</td>
					</tr>
					<!-- NEOX-2989, END-->

					<tr>
						<td>&nbsp;</td>
						<td align="left" class="label">Hostid</td>
						<td align="left" class="label">:</td>
						<td align="left" class="label"><?php echo $inputKey; ?></td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td align="left" class="label" valign="top">License Key</td>
						<td align="left" class="label" valign="top">:</td>
						<td align="left" class="label"><textarea name="license_key" id="license_key" rows="6" cols="55" readonly>
						<?php echo trim($license_key); ?></textarea></td>
						<td>&nbsp;</td>
					</tr>
				</table>
		<?php
			}
		}
		?>
		</table>
</body>
</head>

</html>