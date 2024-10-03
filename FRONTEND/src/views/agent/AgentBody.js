import React from 'react'
import { CCard, CCardBody, CCardHeader, CCardTitle, CCol, CRow, } from '@coreui/react'
import InOutCards from './InOutCards'
import { Grid, Typography, Box, Paper } from '@mui/material'

export default function AgentBody() {

    return (
        <>
            <CRow>
                <CCol className='mb-3'>
                    <CCard>
                        <CCardHeader>
                            <CCardTitle>
                                Customer Info | Script
                            </CCardTitle>
                        </CCardHeader>
                        <CCardBody>
                            <InOutCards />
                            <Grid container>
                                <Grid lg={4}>
                                    <Box className='mb-2' sx={{ backgroundColor: "#F4F4F5", padding: "15px", borderRadius: "10px" }}>
                                        <Grid container alignItems={"center"}>
                                            <Grid mr={"20px"}>
                                                img
                                            </Grid>
                                            <Grid>
                                                <Typography sx={{ fontSize: "24px", fontWeight: "700", fontFamily: 'sans-serif', color: "#1F222A", lineHeight: "31px" }}>8841</Typography>
                                                <Typography sx={{ fontSize: "12px", fontWeight: "600", fontFamily: 'sans-serif', color: "#454954", lineHeight: "31px", textTransform: "uppercase", letterSpacing: "1px" }}>total enterprise</Typography>
                                            </Grid>

                                        </Grid>
                                    </Box>
                                </Grid>
                                <Grid lg={4} px={"10px"}>
                                    <Box className='mb-2' sx={{ backgroundColor: "#E5FFF2", padding: "15px", borderRadius: "10px" }}>
                                        <Grid container alignItems={"center"}>
                                            <Grid mr={"20px"}>
                                                img
                                            </Grid>
                                            <Grid>
                                                <Typography sx={{ fontSize: "24px", fontWeight: "700", fontFamily: 'sans-serif', color: "#1F222A", lineHeight: "31px" }}>6264</Typography>
                                                <Typography sx={{ fontSize: "12px", fontWeight: "600", fontFamily: 'sans-serif', color: "#454954", lineHeight: "31px", textTransform: "uppercase", letterSpacing: "1px" }}>active enterprise</Typography>
                                            </Grid>

                                        </Grid>
                                    </Box>
                                </Grid>
                                <Grid lg={4}>
                                    <Box className='mb-2' sx={{ backgroundColor: "#FFEEEE", padding: "15px", borderRadius: "10px" }}>
                                        <Grid container alignItems={"center"}>
                                            <Grid mr={"20px"}>
                                                img
                                            </Grid>
                                            <Grid>
                                                <Typography sx={{ fontSize: "24px", fontWeight: "700", fontFamily: 'sans-serif', color: "#1F222A", lineHeight: "31px" }}>2557</Typography>
                                                <Typography sx={{ fontSize: "12px", fontWeight: "600", fontFamily: 'sans-serif', color: "#454954", lineHeight: "31px", textTransform: "uppercase", letterSpacing: "1px" }}>inactive enterprise</Typography>
                                            </Grid>

                                        </Grid>
                                    </Box>
                                </Grid>
                            </Grid>
                        </CCardBody>
                    </CCard>
                </CCol>
            </CRow>
        </>
    )
}
