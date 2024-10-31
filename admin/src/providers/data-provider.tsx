import { stringify } from "query-string";
import { DataProvider, fetchUtils } from "ra-core";

const getHeaders = () => {
	return new Headers({
		// @ts-ignore
		"X-WP-Nonce": window.wpApiSettings.nonce,
	});
};

const dataProvider = (
	apiUrl: string,
	httpClient = fetchUtils.fetchJson,
	countHeader = "Content-Range"
): DataProvider => ({
	getList: (resource, params) => {
		const { page, perPage } = params.pagination;
		const { field, order } = params.sort;

		const rangeStart = (page - 1) * perPage;
		const rangeEnd = page * perPage - 1;

		const query = {
			sort: JSON.stringify([field, order]),
			range: JSON.stringify([rangeStart, rangeEnd]),
			filter: JSON.stringify(params.filter),
		};
		const url = `${apiUrl}/${resource}&${stringify(query)}`;
		const headers = getHeaders();
		if (countHeader === "Content-Range") {
			headers.append("Range", `${resource}=${rangeStart}-${rangeEnd}`);
		}

		/*
				const options =
					countHeader === 'Content-Range'
						? {
							// Chrome doesn't return `Content-Range` header if no `Range` is provided in the request.
							headers: new Headers({
								Range: `${resource}=${rangeStart}-${rangeEnd}`,
							}),
						}
						: {};
		*/
		const options = {
			headers,
		};
		return httpClient(url, options).then(({ headers, json }) => {
			if (!headers.has(countHeader)) {
				throw new Error(
					`The ${countHeader} header is missing in the HTTP Response. The simple REST data provider expects responses for lists of resources to contain this header with the total number of results to build the pagination. If you are using CORS, did you declare ${countHeader} in the Access-Control-Expose-Headers header?`
				);
			}
			const contentRange = headers.get("content-range") ?? "";
			return {
				data: json,
				total:
					countHeader === "Content-Range"
						? parseInt(contentRange.split("/").pop() ?? "", 10)
						: parseInt(contentRange),
			};
		});
	},

	getOne: (resource, params) => {
		const headers = getHeaders();
		const options = {
			headers,
		};
		return httpClient(`${apiUrl}/${resource}/${params.id}`, options).then(
			({ json }) => ({
				data: json,
			})
		);
	},

	getMany: (resource, params) => {
		const headers = getHeaders();
		const options = {
			headers,
		};
		const query = {
			filter: JSON.stringify({ id: params.ids }),
		};
		const url = `${apiUrl}/${resource}&${stringify(query)}`;
		return httpClient(url, options).then(({ json }) => ({ data: json }));
	},

	getManyReference: (resource, params) => {
		const { page, perPage } = params.pagination;
		const { field, order } = params.sort;

		const rangeStart = (page - 1) * perPage;
		const rangeEnd = page * perPage - 1;

		const query = {
			sort: JSON.stringify([field, order]),
			range: JSON.stringify([(page - 1) * perPage, page * perPage - 1]),
			filter: JSON.stringify({
				...params.filter,
				[params.target]: params.id,
			}),
		};
		const url = `${apiUrl}/${resource}&${stringify(query)}`;
		const headers = getHeaders();
		if (countHeader === "Content-Range") {
			headers.append("Range", `${resource}=${rangeStart}-${rangeEnd}`);
		}
		const options = {
			headers,
		};
		/*const options =
			countHeader === 'Content-Range'
				? {
					// Chrome doesn't return `Content-Range` header if no `Range` is provided in the request.
					headers: new Headers({
						Range: `${resource}=${rangeStart}-${rangeEnd}`,
					}),
				}
				: {};*/

		return httpClient(url, options).then(({ headers, json }) => {
			if (!headers.has(countHeader)) {
				throw new Error(
					`The ${countHeader} header is missing in the HTTP Response. The simple REST data provider expects responses for lists of resources to contain this header with the total number of results to build the pagination. If you are using CORS, did you declare ${countHeader} in the Access-Control-Expose-Headers header?`
				);
			}
			const contentRange = headers.get("content-range") ?? "";
			return {
				data: json,
				total:
					countHeader === "Content-Range"
						? parseInt(contentRange.split("/").pop() ?? "", 10)
						: parseInt(contentRange),
			};
		});
	},

	update: (resource, params) => {
		const headers = getHeaders();
		return httpClient(`${apiUrl}/${resource}/${params.id}`, {
			method: "PUT",
			body: JSON.stringify(params.data),
			headers,
		}).then(({ json }) => ({ data: json }));
	},

	// simple-rest doesn't handle provide an updateMany route, so we fallback to calling update n times instead
	updateMany: (resource, params) => {
		const headers = getHeaders();
		return Promise.all(
			params.ids.map((id) =>
				httpClient(`${apiUrl}/${resource}/${id}`, {
					method: "PUT",
					body: JSON.stringify(params.data),
					headers,
				})
			)
		).then((responses) => ({ data: responses.map(({ json }) => json.id) }));
	},

	create: (resource, params) => {
		return httpClient(`${apiUrl}/${resource}`, {
			method: "POST",
			body: JSON.stringify(params.data),
			headers: getHeaders(),
		}).then(({ json }) => ({
			data: { ...params.data, id: json.id },
		}));
	},

	delete: (resource, params) => {
		const headers = getHeaders();
		headers.append("Content-Type", "text/plain");
		return httpClient(`${apiUrl}/${resource}/${params.id}`, {
			method: "DELETE",
			headers,
		}).then(({ json }) => ({ data: json }));
	},

	// simple-rest doesn't handle filters on DELETE route, so we fallback to calling DELETE n times instead
	deleteMany: (resource, params) => {
		const headers = getHeaders();
		headers.append("Content-Type", "text/plain");
		return Promise.all(
			params.ids.map((id) =>
				httpClient(`${apiUrl}/${resource}/${id}`, {
					method: "DELETE",
					headers,
				})
			)
		).then((responses) => ({
			data: responses.map(({ json }) => json.id),
		}));
	},
});

export default dataProvider;
