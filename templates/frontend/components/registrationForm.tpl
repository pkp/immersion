{**
 * templates/frontend/components/registrationForm.tpl
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Display the basic registration form fields
 *
 * @uses $locale string Locale key to use in the affiliate field
 * @uses $firstName string First name input entry if available
 * @uses $middleName string Middle name input entry if available
 * @uses $lastName string Last name input entry if available
 * @uses $countries array List of country options
 * @uses $country string The selected country if available
 * @uses $email string Email input entry if available
 * @uses $username string Username input entry if available
 *}
<fieldset class="identity">
	<legend>
		{translate key="user.profile"}
	</legend>
	<div class="row">
		<div class="col-sm-4 form-group">
			<label for="firstName">
				{translate key="user.firstName"}
				<span class="required">*</span>
				<span class="sr-only">
					{translate key="common.required"}
				</span>
			</label>
			<input type="text" class="form-control" name="firstName" id="firstName" value="{$firstName|escape}" maxlength="40" required>
		</div>
		<div class="col-sm-4 form-group">
			<label for="middleName">
				{translate key="user.middleName"}
			</label>
			<input type="text" class=form-control name="middleName" id="middleName" value="{$middleName|escape}" maxlength="40">
		</div>
		<div class="col-sm-4 form-group">
			<label for="lastName">
				{translate key="user.lastName"}
				<span class="required">*</span>
				<span class="sr-only">
					{translate key="common.required"}
				</span>
			</label>
			<input type="text" class="form-control" name="lastName" id="lastName" value="{$lastName|escape}" maxlength="40" required>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label for="affiliation">
				{translate key="user.affiliation"}
				<span class="required">*</span>
				<span class="sr-only">
					{translate key="common.required"}
				</span>
			</label>
			<input type="text" class="form-control" name="affiliation[{$primaryLocale|escape}]" id="affiliation" value="{$affiliation.$primaryLocale|escape}" required>
		</div>

		<div class="col-sm-6 form-group">
			<label for="country">
				{translate key="common.country"}
				<span class="required">*</span>
				<span class="sr-only">
					{translate key="common.required"}
				</span>
			</label>
			<select class="form-control" name="country" id="country" required>
				<option class="choose-country"></option>
				{html_options options=$countries selected=$country}
			</select>
		</div>
	</div>
</fieldset>

<fieldset class="login">
	<legend>
		{translate key="user.login"}
	</legend>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label for="email">
				{translate key="user.email"}
				<span class="required">*</span>
				<span class="sr-only">
					{translate key="common.required"}
				</span>
			</label>
			<input class="form-control" type="text" name="email" id="email" value="{$email|escape}" maxlength="90" required>
		</div>
		<div class="col-sm-6 form-group">
			<label for="username">
				{translate key="user.username"}
				<span class="required">*</span>
				<span class="sr-only">
					{translate key="common.required"}
				</span>
			</label>
			<input class="form-control" type="text" name="username" id="username" value="{$username|escape}" maxlength="32" required>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label for="password">
				{translate key="user.password"}
				<span class="required">*</span>
				<span class="pkp_screen_reader">
					{translate key="common.required"}
				</span>
			</label>
			<input class=form-control type="password" name="password" id="password" password="true" maxlength="32" required>
		</div>
		<div class="col-sm-6 form-group">
			<label for="password">
				{translate key="user.repeatPassword"}
				<span class="required">*</span>
				<span class="sr-only">
					{translate key="common.required"}
				</span>
			</label>
			<input class="form-control" type="password" name="password2" id="password2" password="true" maxlength="32" required>
		</div>
	</div>
</fieldset>
