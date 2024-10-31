import React, { FC, Fragment } from "react";
import { ListProps } from "@material-ui/core";
import {
	BooleanField,
	BooleanInput,
	BulkDeleteButton,
	Create,
	Datagrid,
	Edit,
	EditButton,
	List,
	SimpleForm,
	TextField,
	TextInput,
} from "react-admin";
import { CreateProps } from "ra-core/lib/controller/details/useCreateController";
import { BulkDeleteButtonProps } from "ra-ui-materialui/lib/button/BulkDeleteButton";
import { PublishButton, UnPublishButton } from "../components/Buttons";

/*const FieldsFilter: FC = (props) => (
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
);*/

const FieldsBulkActionButtons: FC<BulkDeleteButtonProps> = (props) => (
	<Fragment>
		<PublishButton {...props} />
		<UnPublishButton {...props} />
		<BulkDeleteButton
			{...props}
			undoable={false}
			confirmContent="This will delete all related states, cities, predefined texts. Are you sure?"
		/>
	</Fragment>
);

export const FieldsList: FC<ListProps> = (props) => {
	return (
		<List
			{...props}
			bulkActionButtons={<FieldsBulkActionButtons />}
			exporter={false}
		>
			<Datagrid>
				<TextField source="id" label="ID" />
				<TextField source="name" label="Name" />
				<BooleanField source="published" label="Published" />
				<EditButton />
			</Datagrid>
		</List>
	);
};

export const FieldsCreate: FC<CreateProps> = (props) => {
	return (
		<Create {...props}>
			<SimpleForm>
				<TextInput source="name" label="Name" resettable />
				<BooleanInput source="published" label="Published" />
			</SimpleForm>
		</Create>
	);
};

export const FieldsEdit: FC = (props) => {
	return (
		<Edit {...props}>
			<SimpleForm>
				<TextInput source="id" label="ID" disabled />
				<TextInput source="name" label="Name" resettable />
				<BooleanInput source="published" label="Published" />
			</SimpleForm>
		</Edit>
	);
};
