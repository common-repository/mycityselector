import React, { FC, Fragment } from "react";
import {
	List,
	Datagrid,
	TextField,
	Create,
	SimpleForm,
	TextInput,
	EditButton,
	Edit,
	ReferenceField,
	ReferenceInput,
	AutocompleteInput,
	BooleanInput,
	BooleanField,
	Filter,
	BulkDeleteButton,
} from "react-admin";
import { BulkDeleteButtonProps } from "ra-ui-materialui/lib/button/BulkDeleteButton";
import { PublishButton, UnPublishButton } from "../components/Buttons";

const ProvincesFilter: FC = (props) => (
	<Filter {...props}>
		<TextInput label="Title" source="title" />
		<ReferenceInput
			reference="Countries"
			source="country_id"
			label="Country"
			filterToQuery={(searchText) => ({ title: searchText })}
			resettable
		>
			<AutocompleteInput optionText="title" />
		</ReferenceInput>
		<BooleanInput source="published" label="Published" />
	</Filter>
);

const ProvincesBulkActionButtons: FC<BulkDeleteButtonProps> = (props) => (
	<Fragment>
		<PublishButton {...props} />
		<UnPublishButton {...props} />
		<BulkDeleteButton
			{...props}
			undoable={false}
			confirmContent="This will delete all related items. Are you sure?"
		/>
	</Fragment>
);

export const ProvincesList: FC = (props) => {
	return (
		<List
			{...props}
			exporter={false}
			filters={<ProvincesFilter />}
			bulkActionButtons={<ProvincesBulkActionButtons />}
			title="States / Provinces"
		>
			<Datagrid>
				<TextField source="id" label="ID" />
				<ReferenceField source="country_id" reference="Countries">
					<TextField source="title" label="Country" />
				</ReferenceField>
				<TextField source="title" label="Title" />
				<TextField source="subdomain" label="Subdomain" />
				<BooleanField source="published" label="Published" />
				<TextField source="ordering" label="Ordering" />
				<EditButton />
			</Datagrid>
		</List>
	);
};

export const ProvincesCreate: FC = (props) => (
	<Create {...props}>
		<SimpleForm>
			<TextInput source="title" label="Title" />
			<ReferenceInput
				label="Country"
				source="country_id"
				reference="Countries"
				filterToQuery={(text) => ({ title: text })}
			>
				<AutocompleteInput optionText="title" />
			</ReferenceInput>
			<TextInput source="subdomain" label="SubDomain" />
			<BooleanInput source="published" label="Published" />
			<TextInput source="ordering" label="Ordering" />
		</SimpleForm>
	</Create>
);

export const ProvincesEdit: FC = (props) => (
	<Edit {...props}>
		<SimpleForm>
			<TextInput source="id" label="ID" disabled />
			<TextInput source="title" label="Title" />
			<ReferenceInput
				label="Country"
				source="country_id"
				reference="Countries"
				filterToQuery={(text) => ({ title: text })}
			>
				<AutocompleteInput optionText="title" />
			</ReferenceInput>
			<TextInput source="subdomain" label="SubDomain" />
			<BooleanInput source="published" label="Published" />
			<TextInput source="ordering" label="Ordering" />
		</SimpleForm>
	</Edit>
);
