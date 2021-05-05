<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" style="margin: 0;padding: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;">
<head style="margin: 0;padding: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;">
	<meta name="viewport" content="width=600" style="margin: 0;padding: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" style="margin: 0;padding: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;">
	<title style="margin: 0;padding: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;">SABAH PARKS</title>
</head>

<body style="margin: 0;padding: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;-webkit-font-smoothing: antialiased;-webkit-text-size-adjust: none;height: 100%;color: #574751;width: 100%!important;">
	<table width="100%" style="margin: 0;padding: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;">
		<tr style="margin: 0;padding: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;">
			<td align="center" style="margin: 0;padding: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;">
				<table width="600" cellpadding="0" border="0" align="center" cellspacing="0" style="margin: 0;padding: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;">
					<tr style="margin: 0;padding: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;">
						<td style="margin: 0;padding: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;">
							<table width="100%" cellpadding="0" border="0" align="center" cellspacing="0" style="margin: 0;padding: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;">
								<tr style="margin: 0;padding: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;">
									<td height="125" align="center" style="margin: 0;padding: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;">
										<a href="" style="margin: 0;padding: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;word-wrap: break-word;color: #f23b51;text-decoration: underline;font-weight: bold;">
											<img src="../main/assets/images/firmus-logo.png" alt="" class="logo" style="max-height: 37px;margin: 5px 0;padding: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;max-width: 100%;">
										</a>
									</td>
								</tr>
							</table>
							<table width="100%" cellpadding="0" border="0" align="center" cellspacing="0" style="margin: 0;padding: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;">
								<tr style="margin: 0;padding: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;">
									<td style="margin: 0;padding: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;">
										<table width="100%" cellpadding="0" border="0" align="center" cellspacing="0" style="margin: 0;padding: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;">
											<tr style="margin: 0;padding: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;">
												<td align="center" style="padding-bottom: 0.5em;margin: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;">
													<h1 style="margin: 0;padding: 0 18px;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 125%;display: block;font-size: 24px;font-style: normal;font-weight: bold;letter-spacing: normal;">{{ $title }}</h1>
												</td>
											</tr>
										@if(!empty($image))
												<tr style="margin: 0;padding: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;">
												<td align="justify" style="padding-bottom: 0;margin: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;">
													<p style="margin: 1em 0;padding: 0 18px;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 150%;font-size: 16px;">
														
													</p>
													<p style="margin: 1em 0;padding: 0 18px;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 150%;font-size: 16px;">
														<img src="{{ $message->embed($image) }}" style="width: 400px; height:250px;"/>
													</p>
												</td>
											</tr>
										@endif
											<tr style="margin: 0;padding: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;">
												<td align="justify" style="padding-bottom: 0;margin: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;">
													<p style="margin: 1em 0;padding: 0 18px;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 150%;font-size: 16px;">
														
													</p>
													<p style="margin: 1em 0;padding: 0 18px;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 150%;font-size: 16px;">
														{!! $content !!}
													</p>
												</td>
											</tr>
											<tr style="margin: 0;padding: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;">
												<td align="justify" style="border-bottom: 1px solid #f1f1f1;padding-bottom: 2em;margin: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;">
													<p style="margin: 1em 0;padding: 0 18px;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 150%;font-size: 16px;">
														Best regards,
														<br/>
														Sabah Parks Customer Service
													</p>
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
							<table width="100%" cellpadding="0" border="0" align="center" cellspacing="0" style="margin: 0;padding: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;">
								<tr style="margin: 0;padding: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;">
									<td align="center" style="padding-top: 1.7em;padding-bottom:1em;margin: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;">
										<small style="margin: 0;padding: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;">
											&copy;&nbsp;2021 SABAH PARKS.
										</small>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</body>
</html>