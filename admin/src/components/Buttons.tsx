import { BooleanField, Button } from "ra-ui-materialui";
import * as React from "react";
import { useNotify, useRefresh, useUnselectAll, useUpdateMany } from "ra-core";
import { FC } from "react";

export const BooleanWrapper: FC = (props: any) => {
	const newProps = {
		...props,
		record: {
			...props.record,
			[props.source]: !!parseInt(props?.record?.[props.source]),
		},
	};
	return <BooleanField {...newProps} />;
};

export const PublishButton: FC = (props: any) => {
	const refresh = useRefresh();
	const notify = useNotify();
	const unselectAll = useUnselectAll();
	const [updateMany, { loading }] = useUpdateMany(
		props.resource,
		props.selectedIds,
		{ published: 1 },
		{
			onSuccess: () => {
				refresh();
				notify("Success");
				unselectAll(props.resource);
			},
			onFailure: (error: any) =>
				notify("Error: " + error.toString(), "warning"),
		}
	);
	return (
		<Button
			label={props.label ?? "Publish"}
			disabled={loading}
			onClick={updateMany}
		/>
	);
};

export const UnPublishButton: FC = (props: any) => {
	const refresh = useRefresh();
	const notify = useNotify();
	const unselectAll = useUnselectAll();
	const [updateMany, { loading }] = useUpdateMany(
		props.resource,
		props.selectedIds,
		{ published: 0 },
		{
			onSuccess: () => {
				refresh();
				notify("Success");
				unselectAll(props.resource);
			},
			onFailure: (error: any) =>
				notify("Error: " + error.toString(), "warning"),
		}
	);
	return (
		<Button
			label={props.label ?? "Unpublish"}
			disabled={loading}
			onClick={updateMany}
		/>
	);
};
