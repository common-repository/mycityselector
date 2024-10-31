import React, { FC, Fragment } from "react";
import {
	List,
	Datagrid,
	TextField,
	Create,
	SimpleForm,
	TextInput,
	BooleanInput,
	EditButton,
	Edit,
	Filter,
	BulkDeleteButton,
	BooleanField,
	Link,
	Button,
} from "react-admin";
import { PublishButton, UnPublishButton } from "../components/Buttons";
import { BulkDeleteButtonProps } from "ra-ui-materialui/lib/button/BulkDeleteButton";

const CountriesFilter: FC = (props) => (
	<Filter {...props}>
		<TextInput label="Title" source="title" resettable />
		<BooleanInput source="published" label="Published" />
	</Filter>
);

const CountriesBulkActionButtons: FC<BulkDeleteButtonProps> = (props) => (
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

const LinkToRelatedProvinces: FC<{
	record?: {
		id?: string;
	};
	label?: string;
}> = ({
	record,
}: {
	record?: {
		id?: string;
	};
}) => {
	return record ? (
		<Button
			color="primary"
			component={Link}
			to={{
				pathname: "/Provinces",
				search: `filter=${JSON.stringify({ country_id: record.id })}`,
			}}
			label="View"
		/>
	) : null;
};

export const CountriesList: FC = (props) => {
	return (
		<List
			{...props}
			filters={<CountriesFilter />}
			bulkActionButtons={<CountriesBulkActionButtons />}
			exporter={false}
		>
			<Datagrid>
				<TextField source="id" label="ID" />
				<TextField source="title" label="Title" />
				<TextField source="subdomain" label="Subdomain" />
				<BooleanField source="published" label="Published" />
				<TextField source="ordering" label="Ordering" />
				<TextField source="code" label="Country Code" />
				<TextField source="domain" label="Domain" />
				<LinkToRelatedProvinces label="States / Provinces" />
				<EditButton />
			</Datagrid>
		</List>
	);
};

export const CountriesCreate: FC = (props) => (
	<Create {...props}>
		<SimpleForm>
			<TextInput source="title" label="Title" />
			<TextInput source="subdomain" label="SubDomain" />
			<BooleanInput source="published" label="Published" />
			<TextInput source="ordering" label="Ordering" />
			<TextInput source="code" label="Country code" />
			<TextInput source="domain" label="Domain" />
		</SimpleForm>
	</Create>
);

export const CountriesEdit: FC = (props) => (
	<Edit {...props}>
		<SimpleForm>
			<TextInput source="id" label="ID" disabled />
			<TextInput source="title" label="Title" />
			<TextInput source="subdomain" label="SubDomain" />
			<BooleanInput source="published" label="Published" />
			<TextInput source="ordering" label="Ordering" />
			<TextInput source="code" label="Country code" />
			<TextInput source="domain" label="Domain" />
		</SimpleForm>
	</Edit>
);
