import React, { useEffect, useMemo, useState } from "react";
import { InputProps } from "@material-ui/core/Input";
import { Loading, Error, useQueryWithStore } from "react-admin";
import Autocomplete from "@material-ui/lab/Autocomplete";
import TextField from "@material-ui/core/TextField";
import _ from "lodash";
import { useField } from "react-final-form";

interface ManyToManyInputProps extends InputProps {
	source: string;
	reference: string;
	through: string;
	using: string;
	label: string;
	record?: {
		id?: number;
	};
	[key: string]: unknown;
}

export const ManyToManyInput: React.FC<ManyToManyInputProps> = (
	props: ManyToManyInputProps
) => {
	const [value, setValue] = useState<{ title: string; id: number }[]>([]);
	const { through, using, record, reference, source, label } = props;
	const fieldNames = useMemo(() => using.split(","), [using]);

	const {
		input: { onChange: onFormValueChange },
	} = useField(source);

	const {
		loaded: refsLoaded,
		error: refsError,
		data: refModelsData,
	} = useQueryWithStore({
		type: "getList",
		resource: reference,
		payload: {
			pagination: { page: 1, perPage: 1000 },
			filter: { published: 1 },
			sort: { field: "title", order: "asc" },
		},
	});

	const { loaded, error, data } = useQueryWithStore({
		type: "getList",
		resource: through,
		payload: {
			pagination: { page: 1, perPage: 1000 },
			filter: { [fieldNames[0]]: record?.id },
			sort: { field: "id", order: "asc" },
		},
	});
	useEffect(() => {
		if (loaded && refsLoaded) {
			const ids = data.map(
				(fieldValue: any) => fieldValue[fieldNames[1]]
			);
			console.log("ids", ids);
			if (!_.isEmpty(ids)) {
				const values = refModelsData.filter((refModelData: any) =>
					ids.includes(refModelData.id)
				);
				setValue(values);
			}
		}
	}, [data, fieldNames, loaded, refModelsData, refsLoaded]);

	if (!refsLoaded || !loaded) {
		return <Loading />;
	}

	if (refsError || error) {
		return <Error error="Error loading data" />;
	}

	const onChange = (
		event: any,
		newValues: { title: string; id: number }[]
	) => {
		const ids = newValues.map((newValue) => newValue.id);
		onFormValueChange(ids);
		setValue(newValues);
	};

	return (
		<Autocomplete
			autoComplete
			title={label}
			multiple
			value={value}
			onChange={onChange}
			options={refModelsData}
			getOptionLabel={(option: { title: string; id: number }) =>
				option?.title
			}
			defaultValue={[]}
			renderInput={(params) => (
				<TextField
					{...params}
					variant="standard"
					label={label}
					placeholder={label}
				/>
			)}
		/>
	);
};
