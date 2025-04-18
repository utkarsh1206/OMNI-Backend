<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate the table to remove any existing data
        DB::table('mail_template')->truncate();

        DB::table('mail_template')->insert([
            [
                'title' => 'Email Address Confirmation',
                'type' => 'email_confirmation',
                'subject' => 'Please Confirm Your E-mail Address. {company_name}',
                'mail_for' => 'request',
                'body' => '<p><strong>Dear {customer_name},</strong></p><p>Please verify your email address so we know that it\'s really you. <a href="{verification_link}">Verify your email address</a></p>',
                'status' => 'Active',
                'is_allow' => 'No',
            ],
            [
                'title' => 'Reset your user account password',
                'type' => 'Forget_Password_on_EzeeCRM',
                'subject' => 'Forget Password',
                'mail_for' => 'request',
                'body' => '<p><strong>Dear {customer_name},</strong></p>\r\n\r\n<p>You&#39;re receiving this email because you requested a password reset. If you did not request this change, you can safely ignore this email.</p>\r\n\r\n<p>To choose a new password and complete your request, please <strong><a href=\"{reset_url}\">Click Here</a></strong></p>\r\n\r\n<p>Also, if you wish to connect with us directly, please call us on <strong><a href=\"tel:+91- {company_number}\">+91 - {company_number}</a></strong> or write to us at <strong><a href=\"mailto:company_email\">company_email</a></strong></p>\r\n\r\n<p>We are excited to have you onboard!</p>\r\n\r\n<p> </p>\r\n\r\n<p>{signature}</p>\r\n',
                'status' => 'Active',
                'is_allow' => 'No',
            ],
            [
                'title' => 'Support Response (Ticket)',
                'type' => 'support_email_response',
                'subject' => '#{ticket_number} Ticket Number is generated against your query.',
                'mail_for' => 'request',
                'body' => '<p><strong>Dear {customer_name},</strong></p>\r\n\r\n<p>Thank you for contacting to <strong>STL Support.</strong></p>\r\n\r\n<p>#{ticket_number} Ticket Number is generated against your query. One of our customer service managers will reach out to you shortly to guide you further</p>\r\n\r\n<p>Also, if you wish to connect with us directly, please call us on 07949135760 or write to us at <strong><a href=\"mailto:{company_email}\">neox.support@stl.tech</a></strong></p>\r\n\r\n<p>We are excited to have you onboard!</p>\r\n\r\n<p> </p>\r\n\r\n<p><strong>Thanks & Regards,</strong><br>\r\nSupport Desk Groups<br>\r\nSterlite Technologies Limited<br>\r\nBlock 2-3, Magnet Corporate Park, Nr. Sola Flyover, Thaltej Ahmedabad , Gujarat - 380059 India<br>\r\nLandline: +91-79-49135778 | Toll Free : 1-800-102-4750 | Skype : neox.support</p>\r\n',
                'status' => 'Active',
               
                'is_allow' => 'No',
            ],
            [
                'title' => 'Ticket Response (Reply)',
                'type' => 'support_email_ticket_reply',
                'subject' => 'Re : Against Your #{ticket_number} - {previous_subject}',
                'mail_for' => 'other',
                'body' => '<p><strong>Dear {customer_name},</strong></p>\r\n\r\n<p>#{ticket_number}</p>\r\n\r\n<p>{reply_message}</p>\r\n\r\n<p>Also, if you wish to connect with us directly, please call us on <strong><a href=\"tel:+91- {company_number}\">+91- {company_number}</a></strong> or write to us at <strong><a href=\"mailto:{company_email}\">{company_email}</a></strong></p>\r\n\r\n<p>We are excited to have you onboard!</p>\r\n\r\n<p>{signature}</p>\r\n',
                'status' => 'Active',
               
                'is_allow' => 'No',
            ],
            [
                'title' => 'Signup on Businessezee',
                'type' => 'signup_email_company',
                'subject' => 'Welcome to ezeeDesk! Lets Get Started',
                'mail_for' => 'request',
                'body' => '<p><strong>Dear {customer_name},</strong></p>\r\n\r\n<p>Welcome to <strong>ezeeDesk.</strong></p>\r\n\r\n<p>One of our customer success managers will reach out to you shortly to guide you further.</p>\r\n\r\n<p>Also, if you wish to connect with us directly, please call us on <strong><a href=\"tel:+91- {company_number}\">+91 - {company_number}</a></strong> or write to us at <strong><a href=\"mailto:{company_email}\">{company_email}</a></strong></p>\r\n\r\n<p>We are excited to have you onboard!</p>\r\n\r\n<p> </p>\r\n\r\n<p>{signature}</p>\r\n',
                'status' => 'Active',
               
                'is_allow' => 'No',
            ],
            [
                'title' => 'Closed Ticket',
                'type' => 'closed_ticket',
                'subject' => 'Your ticket {ticket_number} has been closed',
                'mail_for' => 'request',
                'body' => '<p><strong>Dear {customer_name},</strong></p>\r\n\r\n<p>Your ticket {ticket_number} has been resolved by {agent_name}</p>\r\n\r\n<p>Hope you are statisfied with our service.</p>\r\n\r\n<p>We are eager to learn and improve. Please take the survey to help us to serve better</p>\r\n\r\n<p>Feedback Survey Link: <a href=\"{url_link}\">Click Here</a></p>\r\n\r\n<p>Also, if you wish to connect with us directly, please call us on <strong><a href=\"tel:+91- {company_no}\">+91 - {company_number}</a></strong> or write to us at <strong><a href=\"mailto:support@ezeedesk.com\">{company_email}</a></strong></p>\r\n\r\n<p>We are excited to have you onboard!</p>\r\n\r\n<p>{signature}</p>\r\n',
                'status' => 'Active',
               
                'is_allow' => 'No',
            ],
            [
                'title' => 'Customer Credentials',
                'type' => 'send_credential_mail',
                'subject' => 'Welcome to ezeeDesk! Lets Get Started',
                'mail_for' => 'request',
                'body' => '<p><strong>Dear {customer_name},</strong></p>\r\n\r\n<p>Welcome to <strong>ezeeDesk Portal.</strong></p>\r\n\r\n<p>Following are the crdentials to log tickets regarding your issues/requirements</p>\r\n\r\n<p><strong>Url </strong>: {url}</p>\r\n\r\n<p><strong>Username </strong>: {username}</p>\r\n\r\n<p><strong>Password </strong>: {password}</p>\r\n\r\n<p><strong>Note :</strong> Please makesure that, after login succesfully change your password to make secure</p>\r\n\r\n<p>Also, if you wish to connect with us directly, please call us on <strong><a href=\"tel:+91- {company_number}\">+91 - {company_number}</a></strong> or write to us at <strong><a href=\"mailto:{company_email}\">{company_email}</a></strong></p>\r\n\r\n<p>We are excited to have you onboard!</p>\r\n\r\n<p> </p>\r\n\r\n<p>{signature}</p>\r\n',
                'status' => 'Active',
               
                'is_allow' => 'No',
            ],
            [
                'title' => 'Requester Replies to ticket',
                'type' => 'requester_reply_on_ticket',
                'subject' => 'New Reply Received - #{ticket_number}',
                'mail_for' => 'agent',
                'body' => '<p>Hi {agent_name},</p>\r\n\r\n<p>The {customer_name} customer have responded to the ticket #{ticket_number}.</p>\r\n\r\n<p>{signature}</p>\r\n',
                'status' => 'Active',
               
                'is_allow' => 'No',
            ],
            [
                'title' => 'Agent Replies to ticket',
                'type' => 'agent_reply',
                'subject' => 'New Reply Received - #{ticket_number}',
                'mail_for' => 'request',
                'body' => '<p>Hi {customer_name},</p>\r\n\r\n<p>The {agent_name} agent have responded to your ticket #{ticket_number}.</p>\r\n\r\n<p>Also, if you wish to connect with us directly, please call us on <strong><a href=\"tel:+91- {company_number}\">+91 - {company_number}</a></strong> or write to us at <strong><a href=\"mailto:company_email\">company_email</a></strong></p>\r\n\r\n<p>We are excited to have you onboard!</p>\r\n\r\n<p>{signature}</p>\r\n',
                'status' => 'Active',
               
                'is_allow' => 'No',
            ],
            [
                'title' => 'Agent solves the ticket',
                'type' => 'agent_solve_the_ticket',
                'subject' => 'Your #{ticket_number} has been resolved',
                'mail_for' => 'request',
                'body' => '<p>Dear {{customer_name}},</p>\r\n\r\n<p>Our Support Rep has indicated that your ticket has been Resolved.</p>\r\n\r\n<p>If you believe that the ticket has not been resolved, please reply to this email to automatically reopen the ticket.<br>\r\nIf there is no response from you, we will assume that the ticket has been resolved and the ticket will be automatically closed after 48 hours.</p>\r\n\r\n<p>Also, if you wish to connect with us directly, please call us on <strong><a href=\"tel:+91- {company_number}\">+91 - {company_number}</a></strong> or write to us at <strong><a href=\"mailto:company_email\">company_email</a></strong></p>\r\n\r\n<p>We are excited to have you onboard!</p>\r\n\r\n<p>{signature}</p>\r\n',
                'status' => 'Active',
               
                'is_allow' => 'No',
            ],
            [
                'title' => 'User Activation Mail',
                'type' => 'user_activation_mail',
                'subject' => 'Your email has been verified successfully',
                'mail_for' => 'request',
                'body' => '<p>Dear {customer_name},</p>\r\n\r\n<p>Your email has been verified successfully. Now you can create you support ticket by login with your credentials.</p>\r\n\r\n<p>Also, if you wish to connect with us directly, please call us on <strong><a href=\"tel:+91- {company_number}\">+91 - {company_number}</a></strong> or write to us at <strong><a href=\"mailto:company_email\">company_email</a></strong></p>\r\n\r\n<p>We are excited to have you onboard!</p>\r\n\r\n<p>{signature}</p>\r\n',
                'status' => 'Active',
               
                'is_allow' => 'No',
            ],
            [
                'title' => 'Ticket Assigned to group',
                'type' => 'ticket_assigned_to_grp',
                'subject' => '#{ticket_number} is assigned to group or department',
                'mail_for' => 'agent',
                'body' => '<p>Dear {agent_name},</p>\r\n\r\n<p>New ticket #{ticket_number } is created by {customer_name} and is assigned to {group_or_department}</p>\r\n\r\n<p>Subject : {ticket_subject}</p>\r\n\r\n<p>Description : {ticket_desccription}</p>\r\n\r\n<p>{signature}</p>\r\n',
                'status' => 'Active',
               
                'is_allow' => 'No',
            ],
            [
                'title' => 'Ticket Assigned to agent',
                'type' => 'ticket_assigned_to_agent',
                'subject' => '#{ticket_number} ticket is generated',
                'mail_for' => 'agent',
                'body' => '<p>Dear {agent_name},</p>\r\n\r\n<p>#{ticket_number} ticket is generated by customer {customer_name}. Ticket has been successfully assigned to you. Please kindly attend the query and do the need full things ASAP.</p>\r\n\r\n<p>{signature}</p>\r\n',
                'status' => 'Active',
               
                'is_allow' => 'No',
            ],
            [
                'title' => 'Send Chat Conversation',
                'type' => 'send_chat_conversation',
                'subject' => 'Transcript of chat session with {customer_name} and Agent/Bot from {company_name}',
                'mail_for' => 'other',
                'body' => '<p>Hello {customer_name},</p>\r\n\r\n<p>Following are the requested chat conversation with Agent/Bot and You.</p>\r\n\r\n<p><strong>Date : </strong>{chat_date}</p>\r\n\r\n<p><strong>Total Chat Duration</strong> : {chat_duration}</p>\r\n\r\n<h2><small><strong><big>Chat Transcript</big></strong></small></h2>\r\n\r\n<p>{chat_msg}</p>\r\n\r\n<p>Also, if you wish to connect with us directly, please call us on <strong><a href=\"tel:+91- {company_number}\">+91 - {company_number}</a></strong> or write to us at {company_email}</p>\r\n\r\n<p>We are excited to have you onboard!</p>\r\n\r\n<p>{signature}</p>\r\n',
                'status' => 'Active',
               
                'is_allow' => 'No',
            ],
            [
                'title' => 'Customer Feedback Response - Agent',
                'type' => 'feedback_response',
                'subject' => '{customer_name} has given feedback for you #{ticket_number}',
                'mail_for' => 'agent',
                'body' => '<p>Dear {agent_name},</p>\r\n\r\n<p>Customer has given response to your #{ticket_number}.</p>\r\n\r\n<p>{signature}</p>\r\n',
                'status' => 'Active',
               
                'is_allow' => 'No',
            ],
            [
                'title' => 'Customer Feedback Response - Customer',
                'type' => 'feedback_response',
                'subject' => 'Thank you for your valuable feedback',
                'mail_for' => 'request',
                'body' => '<p>Dear {customer_name},</p>\r\n\r\n<p>Thanks for leaving the time to leave a review ! We aim to delight our customers every time abd I&#39;m so please that we were unable to meet your expectations.</p>\r\n\r\n<p>Also, if you wish to connect with us directly, please call us on <strong><a href=\"tel:+91- {company_number}\">+91 - {company_number}</a></strong> or write to us at <strong><a href=\"mailto:{company_email}\">{company_email}</a></strong></p>\r\n\r\n<p>We are excited to have you onboard!</p>\r\n\r\n<p>{signature}</p>\r\n',
                'status' => 'Active',
               
                'is_allow' => 'No',
            ],
            [
                'title' => 'Support Queries details',
                'type' => 'support_queries_detail',
                'subject' => 'For More Support Queries Details',
                'mail_for' => 'instant',
                'body' => '<p>Dear Customer,</p>\r\n\r\n<p>Thanks for calling our support team ! Our support executive are glad to meet your expectations as soon as possible. Please send the details of our last queries</p>\r\n\r\n<p>Also, if you wish to connect with us directly, please call us on 919922430765 or write to us at <strong><a href=\"mailto:{company_email}\">stlezeedesk@gmail.com</a></strong></p>\r\n\r\n<p>We are excited to have you onboard!</p>\r\n\r\n<p><strong>Thanks & Regards,</strong><br>\r\nSupport Desk Groups<br>\r\nSterlite Technologies Limited<br>\r\nBlock 2-3, Magnet Corporate Park, Nr. Sola Flyover, Thaltej Ahmedabad , Gujarat - 380059 India<br>\r\nLandline: +91-79-49135778 | Toll Free : 1-800-102-4750 | Skype : neox.support</p>\r\n',
                'status' => 'Active',
               
                'is_allow' => 'No',
            ],
            [
                'title' => 'Ticket transfer to agent',
                'type' => 'ticket_transfer_to_agent',
                'subject' => '#{ticket_number} is transfer from {transfer_agent_name}',
                'mail_for' => 'agent',
                'body' => '<p>Dear {agent_name},</p>\r\n\r\n<p>#{ticket_number} is transfer from {transfer_agent_name}. Attain the ticket and do the possiblities operation against the ticket ASAP.</p>\r\n<p>Following ticket subject are as follows :</p>\r\n<p>\r\n<b>Subject : </b> {ticket_subject}\r\n</p>\r\n<p>{signature}</p>\r\n',
                'status' => 'Active',
               
                'is_allow' => 'No',
            ],
            [
                'title' => 'Ticket transfer to engineer',
                'type' => 'ticket_transfer_to_engineer',
                'subject' => '#{ticket_number} is assigned to you for the {site_title} site',
                'mail_for' => 'engineer',
                'body' => '<p>Dear {engg_name},</p>\r\n\r\n<p>#{ticket_number} is assigned to you for the {site_title} site. Following are the site details.</p>\r\n\r\n<p><h6>{site_title}</h6></p>\r\n<p>\r\n<b>Contact Person</b> : {site_contact_person_name} <br/>\r\n<b>Contact Email</b> : {site_contact_person_email} <br/>\r\n<b>Contact Mobile</b> : {site_contact_person_mobile} <br/>\r\n\r\n<b>Location</b> : {site_location}\r\n</p>\r\n\r\n<p>Once you finish your work, click on the <a href={close_link} target=\"_blank\"><b>Link</b></a> to update the status on portal. Add your remarks if possible.</p>\r\n\r\n<p style=\'color:red\'>(Note : Just make sure, after your work complition or before leave the site, click on the given link to update)</p>\r\n\r\n<p>Also, if you wish to connect with us directly, please call us on <strong><a href=\"tel:+91- {company_number}\">+91 - {company_number}</a></strong> or write to us at <strong><a href=\"mailto:{company_email}\">{company_email}</a></strong></p>\r\n\r\n<p>{signature}</p>',
                'status' => 'Active',
               
                'is_allow' => 'No',
            ],
            [
                'title' => 'Ticket Succesfully attained by engineer',
                'type' => 'ticket_successfuly_attained_by_engineer',
                'subject' => '#{ticket_number} is attended successfully',
                'mail_for' => 'agent',
                'body' => '<p>Dear {agent_name},</p>\r\n\r\n<p>#{ticket_number} is assigned by you to {engg_name} for the {site_title} site attended successfully.</p>\r\n\r\n<p>Engineer have submitted following remarks :</p>\r\n\r\n<p>{engineer_remarks}</p>\r\n\r\n<p>{signature}</p>\r\n',
                'status' => 'Active',
               
                'is_allow' => 'No',
            ],
            [
                'title' => 'Task added to different type',
                'type' => 'task_added_to_different_type',
                'subject' => 'Task has been added to {module_type}',
                'mail_for' => 'agent',
                'body' => '<p>Dear {agent_name},</p>\r\n\r\n<p>Task has been added in against to {module_type} for {module_type_name} for the date {task_date}.</p>\r\n\r\n<p>{signature}</p>\r\n',
                'status' => 'Active',
               
                'is_allow' => 'No',
            ],
            [
                'title' => 'Ticket For Department or CC',
                'type' => 'ticket_for_dept_cc',
                'subject' => '{agent_name} has sent an email of #{ticket_number} Ticket',
                'mail_for' => 'other',
                'body' => '<!DOCTYPE html>\r\n<html lang=\"en\" xmlns=\"http://www.w3.org/1999/xhtml\" xmlns:v=\"urn:schemas-microsoft-com:vml\" xmlns:o=\"urn:schemas-microsoft-com:office:office\">\r\n<head>\r\n    <meta charset=\"utf-8\"> <!-- utf-8 works for most cases -->\r\n    <meta name=\"viewport\" content=\"width=device-width\"> <!-- Forcing initial-scale shouldn\'t be necessary -->\r\n    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\"> <!-- Use the latest (edge) version of IE rendering engine -->\r\n    <meta name=\"x-apple-disable-message-reformatting\">  <!-- Disable auto-scale in iOS 10 Mail entirely -->\r\n    <title>Email | STL</title> <!-- The title tag shows in email notifications, like Android 4.4. -->\r\n</head>\r\n<body width=\"100%\" style=\"padding:0; background-color: #222;\">\r\n    <center style=\"width: 100%;  text-align: left; padding:50px 0\">\r\n    <!--[if mso | IE]>\r\n    <table role=\"presentation\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"background-color: #222222;\">\r\n    <tr>\r\n    <td>\r\n    <![endif]-->\r\n        <!-- Email Header : BEGIN -->\r\n        <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" align=\"center\" width=\"600\" style=\"margin: auto; background:#f1f1f1\" class=\"email-container\">\r\n		<!-- 1 Column Text + Button : BEGIN -->\r\n            <tr>\r\n                <td style=\"background-color: #f1f1f1;\" width=\"100%\">\r\n                    <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\r\n                        <tr>\r\n                            <td style=\"padding: 20px 20px 0; font-family: arial, sans-serif; font-size: 18px; line-height: 140%; color: #222;\">\r\n                               Dear User,	   \r\n							</td>\r\n                        </tr>	\r\n						<tr>\r\n                            <td style=\"padding: 10px 20px 0; font-family: arial, sans-serif; font-size: 15px; line-height: 140%; color: #222;\">\r\n                              Following are the ticket details sent by an {agent_name} agent :\r\n							</td>\r\n                        </tr>\r\n						\r\n						<tr>\r\n                            <td style=\"padding:30px 20px 10px; font-weight:bold; font-family: arial, sans-serif; font-size: 15px; line-height: 140%; color: #222;\">\r\n                               <span style=\"color:#1B46C5\">Ticket Number :</span>  #{ticket_number} ({ticket_created})	   \r\n							</td>\r\n                        </tr>\r\n					</table>\r\n                </td>\r\n            </tr>           \r\n	    </table>\r\n		 <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" align=\"center\" width=\"600\" style=\"margin: auto; background:#f1f1f1\" class=\"email-container\">\r\n		<!-- 1 Column Text + Button : BEGIN -->\r\n            <tr>\r\n                <td style=\"background-color: #f1f1f1; font-family: arial, sans-serif; font-size: 13px;\" width=\"100%\">\r\n					<table align=\"center\" width=\"560\" style=\"margin: auto; background:#fff; border:1px solid #ccc;\" cellspacing=\"0\" cellpadding=\"0\">\r\n					\r\n						<tr>\r\n							<td style=\"background:#fff; border-right:1px solid #ccc; padding:10px; border-bottom:1px solid #ccc; width:33%\"><strong>Ticket Type</strong> : {ticket_type}    </td>\r\n							<td style=\"text-align:left;  background:#fff; border-right:1px solid #ccc; padding:10px; border-bottom:1px solid #ccc; width:33%\"><strong>Ticket Priority :</strong> {ticket_priority} </td>\r\n							<td style=\"text-align:left; background:#fff; padding:10px; border-bottom:1px solid #ccc; width:33%\"><strong>Ticket Status :</strong> {ticket_status}</td>\r\n						</tr>\r\n						<tr>\r\n							<td colspan=\"3\" style=\"background:#fff;  padding:10px; border-bottom:1px solid #ccc; width:100%; line-height:1.5\"><strong>Subject :</strong><br/> {ticket_subject}</td>\r\n						</tr>\r\n						<tr>\r\n							<td colspan=\"3\" style=\"background:#fff;  padding:10px; border-bottom:1px solid #ccc; width:100%; line-height:1.5\"><strong>Desciption  :</strong><br/> {ticket_description}</td>\r\n						</tr>\r\n												\r\n					</table>\r\n				</td>\r\n            </tr>   \r\n\r\n			<tr>\r\n				<td style=\"padding: 20px 20px 10px; font-family: arial, sans-serif; font-size: 15px; line-height: 140%; color: #222;\">\r\n					Following are the contact details :\r\n				</td>\r\n			</tr>\r\n			<tr>\r\n                <td style=\"background-color: #f1f1f1; font-family: arial, sans-serif; font-size: 13px;\" width=\"100%\">\r\n					<table align=\"center\" width=\"560\" style=\"margin: auto; background:#fff; border:1px solid #ccc;\" cellspacing=\"0\" cellpadding=\"0\">\r\n					\r\n						<tr>\r\n							<td colspan=\"2\" style=\"background:#fff; padding:10px; border-bottom:1px solid #ccc; width:100%\"><strong>UMR No.</strong> : {customer_umr_no}    </td>				\r\n							\r\n						</tr>\r\n						<tr>\r\n							<td style=\"background:#fff; border-right:1px solid #ccc; padding:10px; border-bottom:1px solid #ccc; width:50%\"><strong>First Name</strong> : {customer_first_name}    </td>\r\n							<td style=\"text-align:left;  background:#fff;  padding:10px; border-bottom:1px solid #ccc; width:50%\"><strong>Last Name</strong> : {customer_last_name}</td> \r\n							\r\n						</tr>\r\n						<tr>\r\n							<td style=\"background:#fff; border-right:1px solid #ccc; padding:10px;  width:50%\"><strong>Mobile No.</strong> : {customer_mobile_number}    </td>\r\n							<td style=\"text-align:left;  background:#fff;  padding:10px; width:50%\"><strong>Email:</strong> {customer_email} </td>\r\n							\r\n						</tr>\r\n						\r\n												\r\n					</table>\r\n				</td>\r\n            </tr> \r\n							\r\n	    </table>\r\n	\r\n		<table align=\"center\" width=\"600\" style=\"margin: auto; background:#f1f1f1\">\r\n			<tr>\r\n				<td style=\"padding: 20px 20px 20px; font-family: arial, sans-serif; font-size: 13px; line-height: 1.6; color: #222; text-align:center; width:40%;\">\r\n					{signature}\r\n				</td>				\r\n			</tr>\r\n		</table>\r\n    </center>\r\n</body>\r\n</html>',
                'status' => 'Active',
               
                'is_allow' => 'No',
            ],
            
        ]);
    }
}
