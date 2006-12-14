<table>
	<tr>
		<td>{VAR:LC_COUNTRY}:</td>
		<td>{VAR:country}</td>
	</tr>
	
	<tr>
		<td>{VAR:LC_FUNCTION_NAME}:</td>
		<td>{VAR:function_name}</td>
	</tr>
	<tr>
		<td>{VAR:LC_ORGANISATION_COMPANY}:</td>
		<td>{VAR:organisation_company}</td>
	</tr>

	<tr>
		<td>{VAR:LC_RESPONSE_DATE}:</td>
		<td>{VAR:response_date}</td>
	</tr>
	<tr>
		<td>{VAR:LC_DECISION_DATE}:</td>
		<td>{VAR:decision_date}</td>
	</tr>
	<tr>
		<td>{VAR:LC_ARR_DATE}:</td>
		<td>{VAR:arrival_date}</td>
	</tr>
	<tr>
		<td>{VAR:LC_DEP_DATE}:</td>
		<td>{VAR:departure_date}</td>
	</tr>
	<tr>
		<td>{VAR:LC_ALTER_DATES}:</td>
		<td>{VAR:open_for_alternative_dates}</td><!-- chbox -->
	</tr>
	<tr>
		<td>{VAR:LC_HAVE_ACC_REQ}:</td>
		<td>{VAR:accommondation_requirements}</td>
	</tr>
	<!-- SELECTED  DATES -->


	<tr>
		<td colspan="2">
			<table border="1">
				<tr>
					<td>
						{VAR:LC_DATE_TYPE}
					</td>
					<td>
						{VAR:LC_ARR_DATE}
					</td>
					<td>
						{VAR:LC_DEP_DATE}
					</td>
				</tr>
				<!-- SUB: DATES_ROW -->
				<tr>					
					<td>{VAR:date_type}</td>
					<td>{VAR:arrival_date}</td>
					<td>{VAR:departure_date}</td>
				</tr>
				<!-- END SUB: DATES_ROW -->
			</table>
		</td>
	</tr>

	<!-- SUB: FLEXIBLE_DATES -->

	<tr>
		<td colspan="2">
			{VAR:LC_FLEXIBLE_DATES}:
		</td>
	</tr>
	<tr>
		<td valign="top">
			{VAR:LC_PATTERN}:
		</td>
		<td>
			<!-- SUB: PATTERN_NO_APP -->
				not app
			<!-- END SUB: PATTERN_NO_APP -->
			<!-- SUB: PATTERN_WDAY -->
				{VAR:wday_from}&nbsp;{VAR:LC_TO}&nbsp;{VAR:wday_to}
			<!-- END SUB: PATTERN_WDAY -->
			<!-- SUB: PATTERN_DAYS -->
				{VAR:days} days.
			<!-- END SUB: PATTERN_DAYS -->
		</td>
	</tr>
	<!-- END SUB: FLEXIBLE_DATES -->
	<tr>
		<td valign="top">{VAR:LC_DATE_COMMENTS}:</td>
		<td>{VAR:date_comments}</td>
	</tr>


	<!-- SUB: NEEDS_ROOMS -->
	<tr>
		<td colspan="2">
			{VAR:LC_NEEDS_ROOMS}
		</td>
	</tr>

	<tr>
		<td>
			{VAR:LC_SINGLE_ROOMS}:
		</td>
		<td>{VAR:single_count}</td>
	</tr>
	<tr>
		<td>
			{VAR:LC_DOUBLE_ROOMS}:
		</td>
		<td>{VAR:double_count}</td>
	</tr>
	<tr>
		<td>
			{VAR:LC_SUITES}:
		</td>
		<td>{VAR:suite_count}</td>
	</tr>
	<tr>
		<td>
			{VAR:LC_ARR_DATE}
		</td>
		<td>
			{VAR:arrival_date}
		</td>
	</tr>
	
	<tr>
		<td>
			{VAR:LC_DEP_DATE}
		</td>
		<td>
			{VAR:departure_date}
		</td>
	</tr>
	<tr>
		<td colspan="2">{VAR:dates_comments}</td>
	</tr>

	<!-- END SUB: NEEDS_ROOMS -->

	<!-- main function -->

	<tr>
		<td colspan="2"><b>{VAR:LC_MAIN_FUNCTION_ROOM}<b/></td>
	</tr>
	<tr>
		<td>{VAR:LC_EVENT_TYPE}:</td>
		<td>{VAR:main_event_type}</td>
	</tr>
	<tr>
		<td>{VAR:LC_DELEGATE_NO}:</td>
		<td>{VAR:main_delegates_no}</td>
	</tr>
	<tr>
		<td>{VAR:LC_TABLE_FORM}:</td>
		<td>{VAR:main_table_form}</td>
	</tr>
	<tr>
		<td>{VAR:LC_TECH_EQUIP}:</td>
		<td>
		<!-- SUB: MAIN_TECH_EQUIP -->
			{VAR:value}<br/>
		<!-- END SUB: MAIN_TECH_EQUIP -->
		</td>
	</tr>
	<tr>
		<td>{VAR:LC_DOOR_SIGN}:</td>
		<td>{VAR:main_door_sign}</td>
	</tr>
	<tr>
		<td>{VAR:LC_PERSON_NO}:</td>
		<td>{VAR:main_person_no}</td>
	</tr>
	<tr>
		<td>{VAR:LC_START_DATETIME}:</td>
		<td>{VAR:main_start}</td>
	</tr>
	<tr>
		<td>{VAR:LC_END_DATETIME}:</td>
		<td>{VAR:main_end}</td>
	</tr>
	<tr>
		<td>{VAR:LC_24H}:</td>
		<td>{VAR:main_24h}</td> <!-- chbox -->
	</tr>

	<tr>
		<td colspan="2"><b>{VAR:LC_MAIN_CATERING}<b/></td>
	</tr>
	<!-- SUB: MAIN_CATERING -->
	<tr>
		<td colspan="2">
			<table border="1">
				<tr>
					<td>{VAR:LC_TYPE}</td>
					<td>{VAR:LC_START_TIME}</td>
					<td>{VAR:LC_END_TIME}</td>
					<td>{VAR:LC_ATTENDEE_NO}<td>
				</tr>
				<!-- SUB: TIMES_ROW -->
				<tr>					
					<td>{VAR:type}</td>
					<td>{VAR:start_time}</td>
					<td>{VAR:end_time}</td>
					<td>{VAR:attendee_no}</td>
				</tr>
				<!-- END SUB: TIMES_ROW -->
			</table>
		</td>
	</tr>

	<!-- END SUB: MAIN_CATERING -->

	<!-- SUB: ADDITIONAL_FUNCTIONS -->
	<tr>
		<td colspan="2">
			<table border="1">
				<tr>
					<td>{VAR:LC_TYPE}</td>
					<td>{VAR:LC_START_TIME}</td>
					<td>{VAR:LC_END_TIME}</td>
					<td>{VAR:LC_ATTENDEE_NO}<td>
				</tr>
				<!-- SUB: ADD_FUNCTION_ROW -->
				<tr>					
					<td>{VAR:type}</td>
					<td>{VAR:start_time}</td>
					<td>{VAR:end_time}</td>
					<td>{VAR:attendee_no}</td>
				</tr>
				<!-- END SUB: ADD_FUNCTION_ROW -->
			</table>
		</td>
	</tr>
	<!-- END SUB: ADDITIONAL_FUNCTIONS -->
	
	<!-- BILLING DATA -->
	<tr>
		<td colspan="2"><b>{VAR:LC_BILLING_DETAILS}</b></td>
	</tr>
	<tr>
		<td>{VAR:LC_COMPANY}</td>
		<td>{VAR:billing_company}</td>
	</tr>
	<tr>
		<td>{VAR:LC_CONTACT}</td>
		<td>{VAR:billing_contact}</td>
	</tr>
	<tr>
		<td>{VAR:LC_STREET}</td>
		<td>{VAR:billing_street}</td>
	</tr>
	<tr>
		<td>{VAR:LC_CITY}</td>
		<td>{VAR:billing_city}</td>
	</tr>
	<tr>
		<td>{VAR:LC_ZIP}</td>
		<td>{VAR:billing_zip}</td>
	</tr>
	<tr>
		<td>{VAR:LC_COUNTRY}</td>
		<td>{VAR:billing_country}</td>
	</tr>
	<tr>
		<td colspan="2">{VAR:LC_CONTACT_INFORMATION}</td>
	</tr>
	<tr>
		<td>{VAR:LC_NAME}</td>
		<td>{VAR:billing_name}</td>
	</tr>
	<tr>
		<td>{VAR:LC_PHONE_NUMBER}</td>
		<td>{VAR:billing_phone_number}</td>
	</tr>
	<tr>
		<td>{VAR:LC_EMAIL}</td>
		<td>{VAR:billing_email}</td>
	</tr>
	<tr>
		<td colspan="2">VALITUD OTSINGUTULEMUSED</td>
	</tr>
	<!-- SUB: SEARCH_RESULT -->
	<tr>
		<td colspan="2">{VAR:caption} ({VAR:address}) - {VAR:single_count}/{VAR:double_count}/{VAR:suite_count}</td>
	</tr>
	<!-- END SUB: SEARCH_RESULT -->
	</td>
	<tr>
		<td colspan="2">kinnita: <input type="checkbox" name="confirm_rfp_submit" id="{VAR:confirm_ch_id}"/></td>
	</tr>
</table>
