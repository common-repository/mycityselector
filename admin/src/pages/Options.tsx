import React, { FC, memo } from "react";
import {
	AutocompleteInput,
	BooleanInput,
	Edit,
	ReferenceInput,
	SimpleForm,
	SelectInput,
	useNotify,
	useRefresh,
	Toolbar,
	SaveButton,
} from "react-admin";
import { Grid } from "@material-ui/core";
import { useFormState } from "react-final-form";

const OptionsToolbar = (props: any) => (
	<Toolbar {...props}>
		<SaveButton />
	</Toolbar>
);

// eslint-disable-next-line react/display-name
const LocationInput = memo((props: any) => {
	const { values } = useFormState();
	// console.log(values.default_location_type);
	let reference = "Cities";
	if (values.default_location_type != null) {
		switch (values.default_location_type.toString()) {
			case "1":
				reference = "Cities";
				break;
			case "2":
				reference = "Provinces";
				break;
			case "3":
				reference = "Countries";
				break;
		}
	}

	return (
		<ReferenceInput
			label="Default location"
			source="default_location_id"
			reference={reference}
			filterToQuery={(text) => ({
				title: text,
				published: 1,
			})}
			resettable
			fullWidth
			{...props}
		>
			<AutocompleteInput
				optionText="title"
				helperText="Only published location can be selected"
			/>
		</ReferenceInput>
	);
});

const GridSimpleForm = (props: any) => {
	return (
		<Grid item xs={12} md={10} lg={6}>
			<SimpleForm {...props} toolbar={<OptionsToolbar />}>
				{/*<TextInput
					source="base_domain"
					label="Base domain (example: wordpress.org)"
					resettable
					fullWidth
				/>*/}
				<SelectInput
					source="default_location_type"
					label="Default location type"
					choices={[
						{ id: "1", name: "City" },
						{
							id: "2",
							name: "Province / State",
						},
						{
							id: "3",
							name: "Country",
						},
					]}
					fullWidth
				/>
				<LocationInput />
				<SelectInput
					source="seo_mode"
					label="SEO mode"
					choices={[
						{ id: "1", name: "Disabled (example: wordpress.org)" },
						{
							id: "2",
							name: "Subdomain mode (example: new-york.wordpress.org)",
						},
						{
							id: "3",
							name: "Subfolder mode (example: wordpress.org/new-york)",
						},
					]}
					fullWidth
				/>
				<BooleanInput
					source="country_choose_enabled"
					label="Country choose enabled"
					fullWidth
					helperText="Allow user select Country as location"
				/>
				<BooleanInput
					source="province_choose_enabled"
					label="Province / State choose enabled"
					fullWidth
					helperText="Allow user select Province / State as location"
				/>
				<SelectInput
					source="ask_mode"
					label="Ask mode"
					choices={[
						{ id: "0", name: "Show dialog with list of locations" },
						{ id: "1", name: "Just show tooltip" },
						{
							id: "2",
							name: "Don't ask, force redirect to detected location",
						},
					]}
					fullWidth
				/>
				<BooleanInput
					source="redirect_next_visits"
					label="Auto redirect user on previously selected location"
					fullWidth
					helperText="If selected, user will be redirected to selected location on next visits"
				/>
				<BooleanInput
					source="log_enabled"
					label="Enable logging"
					fullWidth
					helperText="Enable plugin logging"
				/>
				<BooleanInput
					source="debug_enabled"
					label="Enable debug"
					fullWidth
					helperText="Enable plugin debug mode"
				/>
			</SimpleForm>
		</Grid>
	);
};

export const OptionsEdit: FC = (props) => {
	const notify = useNotify();
	const refresh = useRefresh();
	const onSuccess = () => {
		notify(`Options saved`);
		refresh();
	};
	const onFailure = (error: any) => {
		notify(`Could not save options: ${error.message}`, "error");
	};
	return (
		<Edit
			{...props}
			onSuccess={onSuccess}
			onFailure={onFailure}
			mutationMode="pessimistic"
			title="Edit plugin options"
		>
			<GridSimpleForm />
		</Edit>
	);
};
