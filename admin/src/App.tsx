import mcsDataProvider from "./providers/data-provider";
import { Admin, Resource } from "react-admin";
import {
	CountriesCreate,
	CountriesEdit,
	CountriesList,
} from "./pages/Countries";
import McsLayout from "./components/McsLayout";
import {
	ProvincesCreate,
	ProvincesEdit,
	ProvincesList,
} from "./pages/Provinces";
import { FC } from "react";
import { CitiesCreate, CitiesEdit, CitiesList } from "./pages/Cities";
import PublicIcon from "@material-ui/icons/Public";
import ExploreIcon from "@material-ui/icons/Explore";
import LocationCityIcon from "@material-ui/icons/LocationCity";
import { OptionsEdit } from "./pages/Options";
import { FieldsCreate, FieldsEdit, FieldsList } from "./pages/Fields";
import PostAddIcon from "@material-ui/icons/PostAdd";
import AssignmentIcon from "@material-ui/icons/Assignment";
import {
	FieldValuesCreate,
	FieldValuesEdit,
	FieldValuesList,
} from "./pages/FieldValues";
import React from "react";

const dataProvider = mcsDataProvider("/?rest_route=/mcs/v1");

export const App: FC = () => (
	<Admin dataProvider={dataProvider} layout={McsLayout} disableTelemetry>
		<Resource
			name="Countries"
			list={CountriesList}
			create={CountriesCreate}
			edit={CountriesEdit}
			icon={PublicIcon}
		/>
		<Resource
			name="Provinces"
			options={{ label: "States / Provinces" }}
			list={ProvincesList}
			create={ProvincesCreate}
			edit={ProvincesEdit}
			icon={ExploreIcon}
		/>
		<Resource
			name="Cities"
			list={CitiesList}
			create={CitiesCreate}
			edit={CitiesEdit}
			icon={LocationCityIcon}
		/>
		<Resource
			name="Fields"
			list={FieldsList}
			create={FieldsCreate}
			edit={FieldsEdit}
			icon={PostAddIcon}
		/>
		<Resource
			name="FieldValues"
			list={FieldValuesList}
			create={FieldValuesCreate}
			edit={FieldValuesEdit}
			icon={AssignmentIcon}
			options={{ label: "Field Values" }}
		/>
		<Resource name="CountryFieldValues" />
		<Resource name="ProvinceFieldValues" />
		<Resource name="CityFieldValues" />
		<Resource name="Options" edit={OptionsEdit} />
	</Admin>
);
