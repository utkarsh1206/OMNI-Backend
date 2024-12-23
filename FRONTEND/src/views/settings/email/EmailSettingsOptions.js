// EmailSettingsOptions.js
import React from "react";
import { CCard, CCardBody, CCardTitle, CCol, CRow } from "@coreui/react";
import { useNavigate } from "react-router-dom";

const EmailSettingsOptions = () => {
  const navigate = useNavigate();

  const handleCardClick = (type) => {
    navigate(`/settings/email_config_settings?type=${type}`);
  };

  const options = [
    { type: "gmail", label: "Gmail" },
    { type: "office365", label: "Microsoft Office365" },
    { type: "zoho", label: "Zoho" },
    { type: "custom", label: "Custom" },
  ];

  return (
    <CRow>
      {options.map((option) => (
        <CCol md={3} key={option.type} className="mb-4">
          <CCard
            className="h-100"
            style={{ cursor: "pointer" }}
            onClick={() => handleCardClick(option.type)}
          >
            <CCardBody className="text-center">
              <CCardTitle>{option.label}</CCardTitle>
            </CCardBody>
          </CCard>
        </CCol>
      ))}
    </CRow>
  );
};

export default EmailSettingsOptions;
