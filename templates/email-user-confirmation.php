<?php // Context data are wrapped inside $args variable ?>
<!DOCTYPE html>
<html>
<head>
<title><?php echo $args['subject']; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<style type="text/css">
	/* CLIENT-SPECIFIC STYLES */
	body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
	table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
	img { -ms-interpolation-mode: bicubic; }

	/* RESET STYLES */
	img { outline:none; text-decoration:none; -ms-interpolation-mode:bicubic; width:auto; max-width:100%; clear:both; display:block; }
	table { border-collapse: collapse !important; }
	body { height: 100% !important; margin: 0 !important; padding: 0 !important; width: 100% !important; font-family: "Trebuchet MS"; }

	/* STYLES */
	p { font-size: 16px; font-family: "Trebuchet MS"; text-align: left; color: #4a4a4a; }
	h1 { font-size: 24px; line-height: 24px; margin: 0; text-transform: uppercase; text-align: left; font-weight: 600; letter-spacing: 3.47px; font-family: "Trebuchet MS"; }
	h2 { font-size: 20px; font-weight: 900; line-height: 24px; color: #1C1C1C; margin: 0; font-family: "Trebuchet MS"; text-transform: uppercase; text-align: left; letter-spacing: 2.7px; }
	h3 { font-size: 16px; font-family: "Trebuchet MS"; line-height: 24px; color: #1c1c1c; margin: 0; letter-spacing: 1.5px; font-weight: 600; text-align: left; }
	ol { list-style-type: decimal; font-family: "Trebuchet MS"; font-size: 16px; text-align: left; padding-left: 20px; }
	ul { font-family: "Trebuchet MS"; font-size: 16px; text-align: left; list-style-type: disc; padding-left: 20px; }
	a { text-decoration: none; border-bottom: 1px solid; text-align: left; font-size: 16px; }
	li { color: #4A4A4A; padding-left: 5px; }
	table.w320 { background-color: white; }
	/* Pour les 1 colonne */
	[data-section-wrapper="one-column"] .mobile-block.ui-sortable { padding-left: 35px; padding-right: 35px; padding-top: 30px; }
	[data-section-wrapper="one-column"] img { max-width: 530px; }
	/* Pour les 2 colonnes */
	[data-section-wrapper="two-column"] .mobile-block { width: 230px; margin-left: 30px; margin-right: 30px; }
	[data-section-wrapper="two-column"] img { max-width: 230px; }
	[data-section-wrapper="two-column"] td { padding-top: 15px; }
	/* Pour les 3 colonnes */
	[data-section-wrapper="three-column"] .mobile-block { width: 140px; margin-left: 30px; margin-right: 30px; }


	div { color: #4a4a4a; }
	.button { font-size: 15px; font-family: "Trebuchet MS"; padding-left: 40px; padding-right: 40px; padding-top: 14px; padding-bottom: 13px; letter-spacing: 1.25px; line-height: 14px; min-width: 198px; position: relative; background-color: transparent; border: 1px solid transparent; border-color: #009ee2;
	 color: #009ee2;
	 cursor: pointer;
	 display: inline-block;
	 font-weight: 600;
	 text-align: center;
	 text-transform: uppercase;
	 text-decoration: none;
	 display: block; }

	/* iOS BLUE LINKS */
	a[x-apple-data-detectors] {
			color: inherit !important;
			text-decoration: none !important;
			font-size: inherit !important;
			font-family: inherit !important;
			font-weight: inherit !important;
			line-height: inherit !important;
	}

	/* MEDIA QUERIES */
	/*@media (max-width: 480px) {
		.mobile-hide {
				display: none !important;
		}
		.mobile-center {
				text-align: center !important;
		}
	}*/

	@media (min-width: 1024px) {
		.button {
			width: 50%;
		}
	}
	/* ANDROID CENTER FIX */
	div[style*="margin: 16px 0;"] { margin: 0 !important; }
</style>
</head>
<body style="margin: 0 !important; padding: 0 !important; background-color: #ffffff;" bgcolor="#ffffff">

<table border="0" cellpadding="0" cellspacing="0" width="100%">
	<tr>
		<td align="center" style="background-color: #ffffff;" bgcolor="#ffffff">
			<!--[if (gte mso 9)|(IE)]>
			<table align="center" border="0" cellspacing="0" cellpadding="0" width="600">
			<tr>
			<td align="center" valign="top" width="600">
			<![endif]-->
			<!-- HEADER -->
			<table style="max-width: 600px;width: 100%;" data-section-wrapper>
				<!-- LOGO -->
				<tr data-section="1">
					<td data-section align="center" valign="top" style="font-size:0; padding: 12px 35px;" bgcolor="white">
						<!--[if (gte mso 9)|(IE)]>
						<table align="center" border="0" cellspacing="0" cellpadding="0" width="600">
						<tr>
						<td align="left" valign="top" width="300">
						<![endif]-->


						<div style="display:inline-block; max-width:50%; min-width:100px; vertical-align:top; width:100%; padding-bottom: 8px;">
							<table align="left" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:300px;">
								<tr>
									<td data-slot-container="1" align="left" valign="top" style="font-family: Open Sans, Helvetica, Arial, sans-serif; font-size: 36px; font-weight: 800; line-height: 48px;" class="mobile-center">
										<div data-slot="image">
											<a href="<?php echo $args['site']['url']; ?>" target="_blank" style="text-decoration: none; color: inherit; display: block; border:none;">
												<?php if (empty($args['site']['logo'])) : ?>
													<h1><?php echo $args['site']['name']; ?></h1>
												<?php else : ?>
												<img
													width="<?php echo $args['site']['logo']['width']; ?>"
													height="<?php echo $args['site']['logo']['height']; ?>"
													src="<?php echo $args['site']['logo']['src']; ?>"
													alt="<?php echo $args['site']['logo']['alt']; ?>"
													style="outline:none; text-decoration:none; -ms-interpolation-mode:bicubic; width:<?php echo $args['site']['logo']['width']; ?>px; height:<?php echo $args['site']['logo']['height']; ?>px; max-width:100%; clear:both; display:block; border:none;"
												>
												<?php endif; ?>
											</a>
										</div>
									</td>
								</tr>
							</table>
						</div>
						<!--[if (gte mso 9)|(IE)]>
						</td>
						</tr>
						</table>
						<![endif]-->
					</td>
				</tr>
				<!-- END: LOGO -->

				<!-- BANNER -->
				<!-- <tr data-section="1">
					<td align="center" style="background-color: #F9F9F9;" bgcolor="#F9F9F9">
						<!--[if (gte mso 9)|(IE)]>
						<table align="center" border="0" cellspacing="0" cellpadding="0" width="600">
						<tr>
						<td align="center" valign="top" width="600">
						<![endif]-->
						<!-- <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:600px;">
							<tr>
								<td data-slot-container="1" align="center" style="font-family: Open Sans, Helvetica, Arial, sans-serif; font-size: 16px; font-weight: 400; line-height: 24px; ">
									<div data-slot="text">
										<img date-slot="image" src="<?php echo $args['site']['url']; ?>/images/email/email-banner.jpg" style="outline:none; text-decoration:none; -ms-interpolation-mode:bicubic; width:auto; max-width:100%; clear:both; display:block;"/>
									</div>
								</td>
							</tr>
						</table> -->
						<!--[if (gte mso 9)|(IE)]>
						</td>
						</tr>
						</table>
						<![endif]-->
					<!-- </td>
				</tr> -->
				<!-- END: BANNER -->
			</table>

			<!-- MAIN CONTENT -->
			<table style="max-width: 600px; width: 100%;" data-section-wrapper>
				<tr data-section="1">
					<td align="center"  style="padding: 30px 40px;" bgcolor="white" height="100%" valign="top" width="100%">
					<!--[if (gte mso 9)|(IE)]>
					<table align="center" border="0" cellspacing="0" cellpadding="0" width="600">
					<tr>
					<td align="center" valign="top" width="600">
					<![endif]-->
					<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:600px;">
						<tr>
							<td data-slot-container="1" align="center" style="font-family: Open Sans, Helvetica, Arial, sans-serif; font-size: 20px; font-weight: 400; line-height: 24px;">
							<?php echo $args['content']; ?>
							</td>
						</tr>
					</table>
					<!--[if (gte mso 9)|(IE)]>
					</td>
					</tr>
					</table>
					<![endif]-->
					</td>
				</tr>
			</table>

			<!-- FOOTER -->
			<table style="max-width: 600px;width: 100%;" data-section-wrapper>
				<tr data-section="1">
				<td align="center" style="padding: 35px; background-color: #FAFAFA;" bgcolor="#FAFAFA">
				<!--[if (gte mso 9)|(IE)]>
				<table align="center" border="0" cellspacing="0" cellpadding="0" width="600">
				<tr>
				<td align="center" valign="top" width="600">
				<![endif]-->
				<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:600px;">
					<?php if (!empty($args['site']['logo'])) : ?>
					<tr>
						<td data-slot-container="1" align="center">
							<div data-slot="image" style="margin-bottom: 20px;">
								<a href="<?php echo $args['site']['url']; ?>" target="_blank" style="text-decoration: none; color: inherit; display: inline-block; border: none;">
									<img
										width="<?php echo $args['site']['logo']['width']; ?>"
										height="<?php echo $args['site']['logo']['height']; ?>"
										src="<?php echo $args['site']['logo']['src']; ?>"
										alt="<?php echo $args['site']['logo']['alt']; ?>"
										style="outline:none; text-decoration:none; -ms-interpolation-mode:bicubic; width:100px; max-width:100%; clear:both; display:block; padding-top: 10px;"
									>
								</a>
							</div>
						</td>
					</tr>
					<?php endif; ?>
					<tr>
						<td data-slot-container align="center" style="font-family: Open Sans, Helvetica, Arial, sans-serif; font-size: 14px; font-weight: 400; line-height: 24px; padding: 5px 0 10px 0;">
							<div data-slot="text">
							<p style="font-size: 12px; line-height: 18px; color: #4A4A4A; text-align: center; ">
								<span style="font-style: italic;">Sent by <a href="<?php echo $args['site']['url']; ?>" target="_blank" style="font-size: inherit; text-decoration: none; color: inherit; display: inlint-block; border:none;"><?php echo $args['site']['name']; ?></a></span>
							</p>
							</div>
						</td>
					</tr>
				</table>
				<!--[if (gte mso 9)|(IE)]>
				</td>
				</tr>
				</table>
				<![endif]-->
				</td>
				</tr>
			</table>
		<!--[if (gte mso 9)|(IE)]>
		</td>
		</tr>
		</table>
		<![endif]-->
		</td>
	</tr>
</table>

</body>
</html>
