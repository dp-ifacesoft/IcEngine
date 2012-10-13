<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:template match="*" mode="categories">

	<xsl:variable name="last_level" value="'0'" />
	<xsl:variable name="deep" value="'0'" />

	<xsl:for-each select="/Input/categories/items/*">

		<xsl:choose>
			<xsl:when test="data/level &gt; $last_level">
				<xsl:variable name="deep" value="$deep + 1" />
				<xsl:variable name="last_level" select="data/level" />
			</xsl:when>
			<xsl:when test="data/level &lt; $last_level">
					<xsl:variable name="deep" value="$deep - 1" />
					<xsl:variable name="last_level" select="data/level" />
			</xsl:when>
		</xsl:choose>

		<div>
			<xsl:attribute name="id">category_<xsl:value-of select="fields/id" /></xsl:attribute>
			<xsl:attribute name="style">
				padding-left: 15px;
				<xsl:if test="$last_level &gt; /Input/expand_level">display:none;</xsl:if>
			</xsl:attribute>
			
			<p>
				<a href="javascript:void(0);">
					<xsl:attribute name="onclick">Controller_Admin_Content_Category.toggleSubcategories(<xsl:value-of select="fields/id" />)</xsl:attribute>
					[s]
				</a>
				<a href="javascript:void(0);">
					<xsl:attribute name="onclick">Controller_Admin_Content_Category.toggleSubcontents(<xsl:value-of select="fields/id" />)</xsl:attribute>
					[c]
				</a>
				<xsl:value-of select="fields/title" />
			</p>
			
			<div class="subcategories" style="display:none;">
				<xsl:attribute name="id">category_<xsl:value-of select="fields/id" />_children</xsl:attribute>
				<xsl:comment>div for subcategories</xsl:comment>
			</div>
			<div class="subcontents" style="display:none;">
				<xsl:attribute name="id">category_<xsl:value-of select="fields/id" />_contents</xsl:attribute>
				<xsl:comment>div for subcontents</xsl:comment>
			</div>
		</div>

	</xsl:for-each>
	
</xsl:template>

</xsl:stylesheet>