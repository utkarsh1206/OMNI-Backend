import React, { useState, useEffect } from 'react';
import {
	CCard,
	CCardBody,
	CCardHeader,
	CCol,
	CRow,
	CButton,
	CForm,
	CFormLabel,
	CFormInput,
	CContainer,
} from '@coreui/react';
import { Link } from 'react-router-dom';

const Ingroups = () => {

	return (
		<>
			<CRow>
				<CCol xs="12">
					<CCard className="mb-3">
						<CCardHeader>
							Search for <strong>Inbound Groups</strong>
						</CCardHeader>
						<CCardBody>
							<CForm id='campaignFilter'>
								<CRow className="mb-1">
									<CCol>
										<CRow className="mb-2">
											<CFormLabel htmlFor="ingroup" className="col-sm-3 col-form-label">
												{<small>Ingroup</small>}
											</CFormLabel>
											<CCol>
												<CFormInput
													type="text"
													id="ingroup"
												/>
											</CCol>
										</CRow>
									</CCol>
									<CCol>
										<CRow className="mb-2 align-middle">
											<CFormLabel htmlFor="ingrpName" className="col-sm-3 col-form-label">
												{<small>Name</small>}
											</CFormLabel>
											<CCol>
												<CFormInput
													type="text"
													id="ingrpName"
												/>
											</CCol>
										</CRow>
									</CCol>
									<CCol>
										<CRow className="mb-2 align-middle">
											<CFormLabel htmlFor="time" className="col-sm-3 col-form-label">
												{<small>Time</small>}
											</CFormLabel>
											<CCol>
												<CFormInput
													type="text"
													id="time"
												/>
											</CCol>
										</CRow>
									</CCol>
								</CRow>
								<div className="d-grid gap-2 d-md-flex justify-content-md-end">
									<CButton type="submit" color="primary" form="campaignFilter">
										Submit
									</CButton>
								</div>
							</CForm>
						</CCardBody>
					</CCard>
				</CCol>
			</CRow>
			<h6>Inbound Groups Listing</h6>
			<hr />
			<CContainer>
				<CRow>
					<CCol md={8}>
						<Link to="/inbound/create_ingrps">
							<CButton color="primary" className="mb-4 me-2" size="sm">
								Create Inbound Group
							</CButton>
						</Link>
						<CButton color="primary" className="mb-4 me-2" size="sm">
							Delete Inbound Group
						</CButton>
					</CCol>
				</CRow>
			</CContainer>
		</>
	);
};

export default Ingroups;