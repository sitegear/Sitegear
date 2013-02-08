<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:output
		method="xml"
		indent="yes"
		version="1.0"
		cdata-section-elements="title description short_description"
	/>

	<xsl:template match="foo">
		<xsl:element name="bar">
			<xsl:copy-of select="./*" />
		</xsl:element>
	</xsl:template>

	<!-- etc... -->

</xsl:stylesheet>
